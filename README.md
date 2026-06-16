# UniSellerHub

SaaS-платформа для централизованного управления товарами, остатками и заказами на различных маркетплейсах (Wildberries, Ozon, Yandex Market, Avito) с интеграцией со складскими системами (МойСклад).

## Стек технологий
- **Backend:** Laravel 13, PHP 8.4, PostgreSQL, Redis, RabbitMQ, Horizon, Reverb (Websockets), Scout (Meilisearch).
- **Microservices:** Go 1.23, Python 3.11.
- **Frontend:** Vue 3 (Composition API), Inertia.js v3, Pinia (State Management), Tailwind CSS 4, Vuetify 3, Shadcn/UI.
- **Analytics:** ApexCharts.
- **Testing:** Pest 4.
- **Monitoring:** Prometheus, Grafana.

## Архитектура синхронизации и логика работы

UniSellerHub построен на событийно-ориентированной архитектуре, где МойСклад выступает в роли "источника истины" для данных об остатках.

### Основные принципы:

1.  **МойСклад — Источник Истины (Source of Truth)**:
    *   Все актуальные данные о товарах (названия, характеристики, штрихкоды, цены) и, главное, **остатках** хранятся в МойСклад.
    *   Изменения в МойСклад (поступления, продажи, списания) являются триггером для обновления данных в UniSellerHub и на маркетплейсах.

2.  **Централизованное управление остатками**:
    *   Пользователь может видеть все свои остатки со всех маркетплейсов и МойСклад в едином интерфейсе (`/inventory`).
    *   **Изменение остатка в UniSellerHub**: Если пользователь вручную изменяет остаток товара в приложении, это изменение сначала отправляется в **МойСклад**.
    *   **Распространение изменений**: После успешного обновления в МойСклад, UniSellerHub автоматически обновляет локальную базу данных и **пушит** новый остаток на все подключенные маркетплейсы (WB, Ozon, Yandex Market).

3.  **Двусторонняя синхронизация**:
    *   **Pull (Получение данных)**:
        *   Кнопка "Pull all Stocks from MP" на странице инвентаря: Запускает процесс получения актуальных остатков со всех подключенных маркетплейсов (WB, Ozon, Yandex Market) и МойСклад в локальную БД приложения. Это полезно для первоначального наполнения и периодической сверки.
        *   Кнопка "Sync Products" / "Sync Orders": Получает товары/заказы со всех подключенных маркетплейсов.
    *   **Push (Отправка данных)**:
        *   Кнопка "Sync from MoySklad": Забирает остатки только из МойСклад и **пушит** их на все подключенные маркетплейсы (WB, Ozon, Yandex Market), а также обновляет локальный инвентарь.
        *   Ручное изменение остатка в таблице инвентаря: После обновления в МойСклад, пушит изменение на маркетплейсы.
    *   **Webhooks (Реакция в реальном времени)**:
        *   Внешние системы (МойСклад, Маркетплейсы) отправляют уведомления о событиях (новый заказ, изменение остатка) на webhook-эндпоинты.
        *   UniSellerHub мгновенно реагирует на эти события, обновляет локальные данные и запускает соответствующие процессы (например, пуш остатков на маркетплейсы после изменения в МойСклад).

## Микросервисная архитектура

Для обеспечения высокой производительности и масштабируемости, UniSellerHub использует распределенную систему микросервисов, взаимодействующих через RabbitMQ.

### Основные микросервисы:

1.  **Go Sync Service (`/services/go_sync_service`)**:
    *   Отвечает за высокоскоростную синхронизацию с API маркетплейсов.
    *   Использует конкурентные горутины и строгий Rate Limiting (Token Bucket) для предотвращения блокировок.
2.  **Price Analyzer (`/services/price_analyzer`)**:
    *   Python-сервис на базе Pandas для расчета скорости продаж и прогнозирования складских запасов. Поддерживает пакетную обработку данных для всех продуктов организации.
3. **Telegram Bot (`/services/telegram_bot`)**:
    *   Go-сервис для асинхронной рассылки уведомлений администраторам и пользователям.
4. **Report Generator (`/services/report_generator`)**:
    *   Python-сервис для генерации тяжелых аналитических отчетов в форматах Excel/PDF.

### Схема взаимодействия (Data Flow):
1. Laravel (Оркестратор) ставит задачу в очередь `sync.tasks`.
2. Микросервис выполняет задачу и возвращает результат в общую очередь `sync.results`.
3. Команда Laravel `app:consume-sync-results` обрабатывает ответ, обновляет данные и вызывает события.

### Генерация отчета по анализу цен (Price Analysis Report)
1.  Пользователь инициирует генерацию отчета через UI (например, кнопка "Analyze Prices" на странице продуктов).
2.  Laravel диспатчит Job `InitiatePriceAnalysisReportJob`, который:
    *   Генерирует уникальный `batch_id`.
    *   Собирает данные (SKU, текущий запас, история продаж) для всех продуктов организации.
    *   Сохраняет метаданные батча (ID организации, ID пользователя, статус) во временном хранилище Redis.
    *   Отправляет пакет данных в очередь `price.tasks` RabbitMQ для обработки сервисом `Price Analyzer`.
3.  Сервис `Price Analyzer` обрабатывает пакет данных и отправляет агрегированный результат (для всего батча) обратно в очередь `sync.results`.
4.  Laravel Consumer `app:process-price-analysis-sync-results` слушает очередь `sync.results`:
    *   При получении результата от `Price Analyzer` (`operation: "price_analysis_batch"`):
        *   Сохраняет результаты анализа цен во временном хранилище Redis под ключом `price_analysis:results:{batchId}`.
        *   Обновляет статус батча в Redis.
        *   Диспатчит Job `GenerateReportForBatchJob`.
    *   При получении результата от `Report Generator` (`operation: "report_generation"`):
        *   Обновляет статус батча в Redis, сохраняет `report_filename` и `download_url`.
        *   Диспатчит Job `NotifyUserOfReportJob`.
5.  Job `GenerateReportForBatchJob` извлекает результаты анализа цен из Redis, форматирует их в JSON-формат, ожидаемый `Report Generator`, и отправляет задачу в очередь `report.tasks` RabbitMQ.
6.  Сервис `Report Generator` генерирует Excel-файл и отправляет ссылку на него обратно в очередь `sync.results`.
7.  Job `NotifyUserOfReportJob` извлекает `download_url` из Redis, генерирует подписанную ссылку для скачивания и отправляет уведомление пользователю через `NotificationService`.
8.  После завершения процесса, временные данные батча удаляются из Redis.

### Интерфейс и UX:
- **Dashboard**: Интерактивные графики выручки и распределения заказов (ApexCharts). Виджеты "Inventory Health" для мониторинга товаров, которые заканчиваются.
- **Global Search (Cmd+K)**: Мгновенный поиск по SKU, названиям товаров и ID заказов по всей базе данных (Scout + Meilisearch).
- **Vector/Semantic Search**: Поиск товаров по смыслу с использованием встроенного векторного поиска Laravel 13, PostgreSQL с расширением `pgvector` и эмбеддингов Laravel AI SDK. Позволяет находить релевантные продукты, понимая намерение пользователя, а не только ключевые слова.
- **Real-time Activity Feed**: Живая лента событий синхронизации и новых заказов (Reverb + Spatie Activity Log).
- **Marketplace Context**: Динамический сайдбар с индивидуальными страницами для каждого подключения.
- **Bulk Actions**: Массовая синхронизация выбранных позиций в таблицах.
- **Advanced Filters**: Фильтрация заказов по диапазону дат и множественным статусам.

## Разработка с Mock API

Для разработки и тестирования без реальных API-ключей реализован Mock Layer (`/api/mock`), который имитирует поведение внешних систем.

### Идентификация в Mock Layer:
Mock-слой имитирует авторизацию реальных API для идентификации аккаунта:
-   **Wildberries**: Заголовок `Authorization: <token>`
-   **Ozon**: Заголовки `Client-Id: <client_id>` и `Api-Key: <api_key>`
-   **Yandex Market**: Заголовок `Api-Key: <api_key>` (также используются `campaign_id` и `business_id` из `credentials`)
-   **МойСклад**: Заголовок `Authorization: Bearer <ms_token>`
-   **Avito**: Заголовок `Authorization: Bearer <token>`

Middleware `IdentifyMockMarketplaceAccount` перехватывает эти заголовки, находит соответствующий `mock_marketplace_account_id` и передает его в контроллеры, позволяя каждому тестовому аккаунту работать со своим набором мок-данных.

### Технические особенности Mock Layer:
-   **Latency Simulation**: Имитация сетевой задержки (0.5 - 1.5 сек) для реалистичного поведения.
-   **Rate Limiting**: Ограничение до 60 запросов в минуту на аккаунт (возвращает HTTP 429 Too Many Requests).
-   **Idempotency**: Поддержка заголовка `X-Idempotency-Key` для предотвращения дублирования запросов, изменяющих состояние (например, обновление остатков). Ответы кешируются в Redis.

### Имитация вебхуков:
-   **`php artisan mock:simulate-activity`**: Консольная команда, запускаемая по расписанию (`everyTenSeconds`), которая имитирует внешнюю активность:
    -   Генерирует новые заказы на WB/Ozon/Yandex.
    -   Изменяет остатки в МойСклад.
    -   Отправляет POST-запросы на внутренние webhook-эндпоинты (`/api/webhooks/wb`, `/api/webhooks/ozon`, `/api/webhooks/ms`, `/api/webhooks/avito`), эмулируя поведение реальных сервисов.
-   **Webhook Handlers**: Контроллеры (`WebhookController`) принимают эти запросы, обрабатывают их и запускают соответствующую логику синхронизации.

## Интеграция маркетплейсов (нюансы)

### Wildberries (WB)
-   **Продукты**: Используется Content API v2 (`/content/v2/get/cards/list`) для получения детальной информации о товарах.
-   **Остатки**: API остатков (`/api/v3/stocks`) возвращает `sku` (артикул), а не `nmId` (внешний ID). Логика синхронизации учитывает это, пытаясь найти листинг сначала по `external_id`, затем по `vendor_code` (SKU).
-   **Обновление остатков**: Используется `PUT /api/v3/stocks/{warehouseId}`.

### Ozon
-   **Продукты**: Получение товаров происходит в два этапа: сначала список `product_id` и `offer_id` (`/v1/product/list`), затем детали по этим ID (`/v1/product/info/list`).
-   **Остатки**: API остатков (`/v1/product/info/stocks`) возвращает `product_id` и `offer_id`.
-   **Обновление остатков**: Используется `POST /v1/product/import/stocks`.

### Yandex Market
-   **Структура**: Использует понятия `businessId` (для товаров и цен) и `campaignId` (для заказов).
-   **Продукты**: Получение через `POST /v2/businesses/{businessId}/offer-mappings`.
-   **Остатки**: Получение и обновление через `POST /v2/businesses/{businessId}/offers/stocks`.
-   **Авторизация**: Использует `Api-Key` in header.

### Avito
-   **Остатки**: Синхронизация остатков по `itemId` через Avito API.

### МойСклад
-   **Продукты/Остатки/Заказы**: Использует `/api/remap/1.2/entity/assortment`, `/report/stock/all`, `/entity/customerorder`.
-   **Авторизация**: Использует Bearer-токен.
-   **Источник истины**: Остатки из МойСклад являются приоритетными при синхронизации.

## Уведомления в реальном времени (Real-time Notifications)

Приложение использует Laravel Reverb для бродкастинга событий и Vue-компоненты для отображения уведомлений:
-   **`UserNotification`**: Событие, отправляемое пользователю через приватный канал.
-   **`NotificationService`**: Сервис для отправки уведомлений конкретному пользователю или всем пользователям организации.
-   **`AppShell.vue`**: Глобальный компонент, который слушает события `user.notification` и отображает их через `Snackbar`.
-   **Flash-сообщения**: При ручном запуске синхронизации через UI, Inertia Flash-сообщения используются для немедленной обратной связи.

## Настройка и запуск

1.  **Клонируйте репозиторий:**
    ```bash
    git clone https://github.com/a1lan1/unisellerhub.git
    ```

2.  **Настройте `.env` файл:**
    Скопируйте `.env.example` в `.env` и настройте подключение к базе данных, Redis, RabbitMQ и Telegram.
    Убедитесь, что переменные для Mock API и Scout настроены корректно:
    ```dotenv
    # Marketplace Mock Endpoints
    WB_BASE_URL="${APP_URL}/api/mock/wb"
    OZON_BASE_URL="${APP_URL}/api/mock/ozon"
    YANDEX_BASE_URL="${APP_URL}/api/mock/yandex"
    MOYSKLAD_BASE_URL="${APP_URL}/api/mock/ms"
    # Telegram Bot
    TELEGRAM_BOT_TOKEN=your_token
    TELEGRAM_ADMIN_CHAT_ID=your_chat_id
    ```

3.  **Запуск в Docker:**
    ```bash
    make install
    ```

4.  **Важно! Запустите Consumer результатов в Laravel:**
    Для того чтобы приложение начало принимать ответы от микросервисов (Go/Python), выполните:
    ```bash
    docker compose exec app php artisan app:consume-sync-results
    ```

5.  **Выполните миграции и сидеры:**
    ```bash
    docker compose exec app php artisan migrate:fresh --seed
    ```

## Мониторинг и Управление
- **Prometheus:** `http://localhost:9090`
- **Grafana:** `http://localhost:3000` (Стандартные дашборды: "Horizon", "Marketplace Sync Overview")
- **RabbitMQ UI:** `http://localhost:15672` (Логин/Пароль: guest/guest)
