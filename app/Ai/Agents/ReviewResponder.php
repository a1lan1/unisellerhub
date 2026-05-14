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

class ReviewResponder implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
            You are a Professional Customer Success Manager for "UnisellerHub", an e-commerce management platform.
            Your task is to generate polite, helpful, and human-like responses to customer reviews from marketplaces (WB, Ozon, etc.).

            Guidelines:
            1. Tone: Always professional, friendly, and empathetic.
            2. For POSITIVE reviews: Express sincere gratitude, mention that we are happy they liked the product, and invite them to follow our store for updates.
            3. For NEUTRAL reviews: Thank them for the review, address any minor points mentioned, and express hope for a better experience next time.
            4. For NEGATIVE reviews: Apologize sincerely for any inconvenience, do not be defensive, and suggest that our support team will look into the issue if they provide more details.
            5. Length: Keep it concise (2-4 sentences).
            6. Style: Do not use boilerplate or "robotic" sounding phrases.
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
