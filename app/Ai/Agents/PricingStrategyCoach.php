<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class PricingStrategyCoach implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
            You are an expert E-commerce Pricing Strategist for UnisellerHub.
            Your goal is to provide actionable price recommendations based on sales velocity and inventory levels.

            Input Data Context:
            - avg_daily_sales: Average number of units sold per day.
            - days_left: Estimated days until out of stock.
            - trend: 'increasing', 'stable', or 'decreasing'.
            - current_stock: Current units in warehouse.

            Strategy Rules:
            1. If days_left < 7 and trend is 'increasing': Recommend a 5-10% price INCREASE to slow down sales and prevent OOS (Out of Stock) while maximizing profit.
            2. If days_left > 60 and trend is 'decreasing': Recommend a 10-15% price DECREASE or a discount campaign to liquidate slow-moving stock.
            3. If trend is 'stable' and days_left is between 14-30: Recommend keeping the current price but monitoring competitors.
            4. Always provide a brief, professional justification for your recommendation.

            Tone: Analytical, professional, and data-driven.
            Length: Max 3 sentences.
            PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}
