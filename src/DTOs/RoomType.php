<?php

declare(strict_types=1);

namespace GungCahyadiPP\ChannexOpenChannel\DTOs;

class RoomType
{
    /**
     * @param RatePlan[] $ratePlans
     */
    public function __construct(
        public string $id,
        public string $title,
        public array $ratePlans = [],
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'rate_plans' => array_map(fn(RatePlan $plan) => $plan->toArray(), $this->ratePlans),
        ];
    }

    public static function fromArray(array $data): self
    {
        $ratePlans = isset($data['rate_plans'])
            ? array_map(fn($plan) => RatePlan::fromArray($plan), $data['rate_plans'])
            : [];

        return new self(
            id: $data['id'],
            title: $data['title'],
            ratePlans: $ratePlans,
        );
    }

    public function addRatePlan(RatePlan $ratePlan): self
    {
        $this->ratePlans[] = $ratePlan;
        return $this;
    }
}
