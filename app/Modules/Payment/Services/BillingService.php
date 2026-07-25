<?php

namespace App\Modules\Payment\Services;

use App\Modules\Payment\Models\{SubscriptionPlan, Subscription, SubscriptionHistory};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BillingService
{
    public function listPlans(int $perPage): LengthAwarePaginator { return SubscriptionPlan::paginate($perPage); }
    public function createPlan(array $data) { return SubscriptionPlan::create($data); }
    public function updatePlan(SubscriptionPlan $plan, array $data) { $plan->update($data); return $plan->fresh(); }
    public function deletePlan(SubscriptionPlan $plan) { $plan->delete(); }

    public function subscribe(array $data): Subscription { return Subscription::create($data); }
    public function listSubscriptions(int $perPage): LengthAwarePaginator { return Subscription::with('plan')->paginate($perPage); }

    public function findPlan(SubscriptionPlan $plan) { return $plan->load('features', 'subscriptions'); }
}
