<?php

namespace App\Core;

class Gate
{
    private array $policies = [];

    public function policy(string $modelClass, string $policyClass): void
    {
        $this->policies[$modelClass] = $policyClass;
    }

    public function allows(string $action, mixed $resource): bool
    {
        $user = [
            'id' => (int) ($_SESSION['user_id'] ?? 0),
            'role' => (string) ($_SESSION['user_role'] ?? 'guest'),
        ];

        if ($user['id'] === 0) {
            return false;
        }
        $resourceClass = is_object($resource) ? get_class($resource) : (is_array($resource) ? ($resource['_type'] ?? 'array') : gettype($resource));

        if (!isset($this->policies[$resourceClass])) {
            return false;
        }

        $policyClass = $this->policies[$resourceClass];
        $policy = new $policyClass();

        if (method_exists($policy, $action)) {
            return $policy->$action($user, $resource);
        }

        return false;
    }
}
