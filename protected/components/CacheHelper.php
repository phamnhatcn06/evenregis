<?php

/**
 * CacheHelper - Centralized cache management for Yii 1.x
 *
 * Cache key patterns:
 * - menu:{role}:{tokenHash}     - Sidebar menu by role (1h)
 * - header:{userId}             - Header user info (30m)
 * - dropdown:{type}             - Dropdown lists (6h)
 * - api:{endpoint}:{hash}       - API responses (15m)
 * - attendees:event:{id}        - Attendees by event (1h)
 * - perms:{tokenHash}           - User permissions (30m)
 */
class CacheHelper
{
    // TTL constants (seconds)
    const TTL_SHORT = 900;        // 15 minutes - API responses
    const TTL_MEDIUM = 1800;      // 30 minutes - User info, permissions
    const TTL_LONG = 3600;        // 1 hour - Menu, attendees
    const TTL_EXTENDED = 21600;   // 6 hours - Dropdown, static data

    // Cache key prefixes
    const PREFIX_MENU = 'menu';
    const PREFIX_HEADER = 'header';
    const PREFIX_DROPDOWN = 'dropdown';
    const PREFIX_API = 'api';
    const PREFIX_ATTENDEES = 'attendees';
    const PREFIX_PERMS = 'perms';
    const PREFIX_FOOTER = 'footer';

    /**
     * Get or set cache with callback
     *
     * @param string $key Cache key
     * @param callable $fetcher Callback to fetch data if not cached
     * @param int $ttl Time to live in seconds
     * @return mixed Cached or fetched data
     */
    public static function getOrSet($key, $fetcher, $ttl = self::TTL_LONG)
    {
        $cache = Yii::app()->cache;
        if ($cache === null) {
            return call_user_func($fetcher);
        }

        $data = $cache->get($key);
        if ($data === false) {
            $data = call_user_func($fetcher);
            if ($data !== null && $data !== false) {
                $cache->set($key, $data, $ttl);
            }
        }
        return $data;
    }

    /**
     * Get cached value
     */
    public static function get($key)
    {
        $cache = Yii::app()->cache;
        if ($cache === null) {
            return false;
        }
        return $cache->get($key);
    }

    /**
     * Set cache value
     */
    public static function set($key, $value, $ttl = self::TTL_LONG)
    {
        $cache = Yii::app()->cache;
        if ($cache === null) {
            return false;
        }
        return $cache->set($key, $value, $ttl);
    }

    /**
     * Delete cache by key
     */
    public static function delete($key)
    {
        $cache = Yii::app()->cache;
        if ($cache === null) {
            return false;
        }
        return $cache->delete($key);
    }

    /**
     * Flush all cache
     */
    public static function flush()
    {
        $cache = Yii::app()->cache;
        if ($cache === null) {
            return false;
        }
        return $cache->flush();
    }

    // ========================================
    // MENU CACHE
    // ========================================

    /**
     * Get menu cache key for current user
     */
    public static function getMenuKey()
    {
        $ssoToken = Yii::app()->session['sso_token'];
        $tokenHash = $ssoToken ? md5($ssoToken) : 'guest';
        return self::PREFIX_MENU . ':' . $tokenHash;
    }

    /**
     * Get cached menu tree
     */
    public static function getMenu($permissions)
    {
        $key = self::getMenuKey();
        return self::getOrSet($key, function () use ($permissions) {
            return MenuHelper::buildMenuTree($permissions);
        }, self::TTL_LONG);
    }

    /**
     * Clear menu cache for current user
     */
    public static function clearMenuCache()
    {
        return self::delete(self::getMenuKey());
    }

    // ========================================
    // HEADER/USER INFO CACHE
    // ========================================

    /**
     * Get header cache key
     */
    public static function getHeaderKey()
    {
        $ssoToken = Yii::app()->session['sso_token'];
        $tokenHash = $ssoToken ? md5($ssoToken) : 'guest';
        return self::PREFIX_HEADER . ':' . $tokenHash;
    }

    /**
     * Get cached header user data
     */
    public static function getHeader()
    {
        $key = self::getHeaderKey();
        return self::getOrSet($key, function () {
            return AuthHandler::getUser();
        }, self::TTL_MEDIUM);
    }

    /**
     * Clear header cache
     */
    public static function clearHeaderCache()
    {
        return self::delete(self::getHeaderKey());
    }

    // ========================================
    // PERMISSIONS CACHE
    // ========================================

    /**
     * Get permissions cache key
     */
    public static function getPermsKey()
    {
        $ssoToken = Yii::app()->session['sso_token'];
        $tokenHash = $ssoToken ? md5($ssoToken) : 'guest';
        return self::PREFIX_PERMS . ':' . $tokenHash;
    }

    /**
     * Get cached permissions
     */
    public static function getPermissions()
    {
        $key = self::getPermsKey();
        return self::getOrSet($key, function () {
            return PermissionHelper::getMenuPermissions();
        }, self::TTL_MEDIUM);
    }

    /**
     * Clear permissions cache
     */
    public static function clearPermsCache()
    {
        return self::delete(self::getPermsKey());
    }

    // ========================================
    // DROPDOWN CACHE
    // ========================================

    /**
     * Get dropdown cache key
     */
    public static function getDropdownKey($type)
    {
        return self::PREFIX_DROPDOWN . ':' . $type;
    }

    /**
     * Get cached dropdown list
     */
    public static function getDropdown($type, $fetcher)
    {
        $key = self::getDropdownKey($type);
        return self::getOrSet($key, $fetcher, self::TTL_EXTENDED);
    }

    /**
     * Clear dropdown cache
     */
    public static function clearDropdownCache($type = null)
    {
        if ($type !== null) {
            return self::delete(self::getDropdownKey($type));
        }
        return true;
    }

    // ========================================
    // API RESPONSE CACHE
    // ========================================

    /**
     * Get API cache key
     */
    public static function getApiKey($endpoint, $params = array())
    {
        $hash = md5($endpoint . serialize($params));
        return self::PREFIX_API . ':' . $hash;
    }

    /**
     * Get cached API response
     */
    public static function getApiResponse($endpoint, $params, $fetcher)
    {
        $key = self::getApiKey($endpoint, $params);
        return self::getOrSet($key, $fetcher, self::TTL_SHORT);
    }

    /**
     * Clear API cache by endpoint
     */
    public static function clearApiCache($endpoint, $params = array())
    {
        return self::delete(self::getApiKey($endpoint, $params));
    }

    // ========================================
    // ATTENDEES CACHE
    // ========================================

    /**
     * Get attendees cache key
     */
    public static function getAttendeesKey($eventId)
    {
        return self::PREFIX_ATTENDEES . ':event:' . $eventId;
    }

    /**
     * Get cached attendees
     */
    public static function getAttendees($eventId, $fetcher)
    {
        $key = self::getAttendeesKey($eventId);
        return self::getOrSet($key, $fetcher, self::TTL_LONG);
    }

    /**
     * Clear attendees cache
     */
    public static function clearAttendeesCache($eventId)
    {
        return self::delete(self::getAttendeesKey($eventId));
    }

    // ========================================
    // CLEAR ALL USER CACHE
    // ========================================

    /**
     * Clear all cache for current user
     * Used for "Clear my cache" button
     */
    public static function clearUserCache()
    {
        $ssoToken = Yii::app()->session['sso_token'];
        $tokenHash = $ssoToken ? md5($ssoToken) : 'guest';

        $results = array();
        $results['menu'] = self::delete(self::PREFIX_MENU . ':' . $tokenHash);
        $results['header'] = self::delete(self::PREFIX_HEADER . ':' . $tokenHash);
        $results['perms'] = self::delete(self::PREFIX_PERMS . ':' . $tokenHash);

        return $results;
    }

    /**
     * Get cache statistics for admin
     */
    public static function getCacheStats()
    {
        $cache = Yii::app()->cache;
        if ($cache === null) {
            return array('enabled' => false);
        }

        $stats = array(
            'enabled' => true,
            'type' => get_class($cache),
        );

        // Get Memcache stats if available
        if ($cache instanceof CMemCache) {
            $servers = $cache->getServers();
            $stats['servers'] = count($servers);
        }

        return $stats;
    }

    /**
     * Clear all static data cache (dropdowns, lists)
     * Used for admin "Clear all cache" button
     */
    public static function clearAllStaticCache()
    {
        $keys = array(
            'events_active',
            'properties_active',
            'roles_active',
            'competitions_active',
            'contents_active',
            'regionals_active',
            'sports_tree',
            'sports_parent',
        );

        $results = array();
        foreach ($keys as $key) {
            $results[$key] = self::delete(self::PREFIX_DROPDOWN . ':' . $key);
        }
        return $results;
    }
}
