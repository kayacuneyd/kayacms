<?php

namespace App\Libraries;

/**
 * Hooks — lightweight WordPress-style filter/action system.
 *
 * Filters transform a value; actions trigger side effects. Callbacks are
 * registered once per request via Hooks::addFilter/addAction and dispatched
 * with Hooks::applyFilters/doAction. Priorities control execution order
 * (lower numbers run first, default 10).
 */
class Hooks
{
    /** @var array<string, list<array{callback: callable, priority: int}>> */
    private static array $filters = [];

    /** @var array<string, list<array{callback: callable, priority: int}>> */
    private static array $actions = [];

    /**
     * Register a filter callback for the given hook.
     */
    public static function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        self::$filters[$hook][] = ['callback' => $callback, 'priority' => $priority];
        self::sort(self::$filters[$hook]);
    }

    /**
     * Run all filters for $hook against $value; each callback receives
     * ($value, ...$args) and must return the (possibly modified) value.
     */
    public static function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        foreach (self::$filters[$hook] ?? [] as $filter) {
            $value = ($filter['callback'])($value, ...$args);
        }

        return $value;
    }

    /**
     * Register an action callback for the given hook.
     */
    public static function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        self::$actions[$hook][] = ['callback' => $callback, 'priority' => $priority];
        self::sort(self::$actions[$hook]);
    }

    /**
     * Fire all action callbacks registered for $hook; each receives (...$args).
     */
    public static function doAction(string $hook, mixed ...$args): void
    {
        foreach (self::$actions[$hook] ?? [] as $action) {
            ($action['callback'])(...$args);
        }
    }

    /**
     * Return true if any callback is registered for the hook.
     */
    public static function hasFilter(string $hook): bool
    {
        return isset(self::$filters[$hook]);
    }

    public static function hasAction(string $hook): bool
    {
        return isset(self::$actions[$hook]);
    }

    /**
     * List all registered filters/actions, grouped by hook (admin screen).
     *
     * @return array{filter: array<string,int>, action: array<string,int>}
     */
    public static function registered(): array
    {
        return [
            'filter' => array_map('count', self::$filters),
            'action' => array_map('count', self::$actions),
        ];
    }

    /**
     * Remove all registered callbacks (test isolation / reset).
     */
    public static function reset(): void
    {
        self::$filters = [];
        self::$actions = [];
    }

    /**
     * Sort a hook's callbacks by priority (stable: same priority keeps order).
     */
    private static function sort(array &$list): void
    {
        usort($list, static fn ($a, $b) => $a['priority'] <=> $b['priority']);
    }
}