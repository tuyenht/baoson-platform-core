<?php

namespace App\Services\Menu;

use App\Models\AdminMenuItem;
use App\Models\User;
use App\Services\FeatureFlags\FeatureFlagService;
use Illuminate\Support\Collection;

final class AdminMenuResolver
{
    /**
     * Resolve menu items for a user.
     * Returns a tree structure of menu items that the user has access to.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function resolve(User $user): array
    {
        // Load all enabled menu items ordered by parent_id and sort_order
        $items = AdminMenuItem::query()
            ->where('is_enabled', true)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();

        // Get user's permissions and roles
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();
        $userRoles = $user->getRoleNames()->toArray();
        $enabledFlags = FeatureFlagService::allEnabledKeys('global');

        // Filter items by permissions, roles, and feature flags
        $filtered = $items->filter(function (AdminMenuItem $item) use ($userPermissions, $userRoles, $enabledFlags) {
            // Check feature flags (ALL must be enabled)
            if ($item->required_flags) {
                foreach ($item->required_flags as $flag) {
                    if (! in_array($flag, $enabledFlags, true)) {
                        return false;
                    }
                }
            }

            // Check roles (ANY-of)
            if ($item->required_roles) {
                $hasRole = false;
                foreach ($item->required_roles as $role) {
                    if (in_array($role, $userRoles, true)) {
                        $hasRole = true;
                        break;
                    }
                }
                if (! $hasRole) {
                    return false;
                }
            }

            // Check permissions (ANY-of)
            if ($item->required_permissions) {
                $hasPermission = false;
                foreach ($item->required_permissions as $permission) {
                    if (in_array($permission, $userPermissions, true)) {
                        $hasPermission = true;
                        break;
                    }
                }
                if (! $hasPermission) {
                    return false;
                }
            }

            return true;
        });

        // Build tree structure
        return self::buildTree($filtered);
    }

    /**
     * Build a tree structure from a flat collection of menu items.
     *
     * @param Collection<int, AdminMenuItem> $items
     * @return array<int, array<string, mixed>>
     */
    private static function buildTree(Collection $items): array
    {
        $tree = [];
        $itemsById = $items->keyBy('id');

        foreach ($items as $item) {
            $menuItem = [
                'key' => $item->key,
                'label' => $item->label,
                'icon' => $item->icon,
                'url' => self::resolveUrl($item),
                'children' => [],
            ];

            if ($item->parent_id && isset($itemsById[$item->parent_id])) {
                // This is a child, add to parent's children
                // We'll handle this in a second pass
                continue;
            } else {
                // This is a root item
                $tree[] = $menuItem;
            }
        }

        // Second pass: add children
        foreach ($items as $item) {
            if ($item->parent_id && isset($itemsById[$item->parent_id])) {
                $parent = $itemsById[$item->parent_id];
                $menuItem = [
                    'key' => $item->key,
                    'label' => $item->label,
                    'icon' => $item->icon,
                    'url' => self::resolveUrl($item),
                    'children' => [],
                ];

                // Find parent in tree and add child
                self::addChildToTree($tree, $parent->key, $menuItem);
            }
        }

        // Remove empty parents
        $tree = array_filter($tree, function ($item) {
            return ! empty($item['children']) || ! empty($item['url']);
        });

        return array_values($tree);
    }

    /**
     * Add a child to a parent in the tree.
     */
    private static function addChildToTree(array &$tree, string $parentKey, array $child): void
    {
        foreach ($tree as &$item) {
            if ($item['key'] === $parentKey) {
                $item['children'][] = $child;
                return;
            }
            if (! empty($item['children'])) {
                self::addChildToTree($item['children'], $parentKey, $child);
            }
        }
    }

    /**
     * Resolve URL from route_name or url field.
     */
    private static function resolveUrl(AdminMenuItem $item): ?string
    {
        if ($item->route_name) {
            try {
                return route($item->route_name);
            } catch (\Throwable) {
                // Route doesn't exist, fallback to url field
            }
        }

        return $item->url;
    }
}

