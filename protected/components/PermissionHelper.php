<?php

/**
 * PermissionHelper - Check CRUD permissions from SSO
 *
 * Permission format: "C R U D" (1=có quyền, 0=không)
 * Example: "1 1 1 0" = Create, Read, Update allowed; Delete denied
 *
 * Position mapping:
 *   0 = Create (create, store)
 *   1 = Read (index, view, list)
 *   2 = Update (edit, update)
 *   3 = Delete (delete, destroy)
 */
class PermissionHelper
{
    const CREATE = 0;
    const READ = 1;
    const UPDATE = 2;
    const DELETE = 3;

    private static $operationMap = array(
        'create' => self::CREATE,
        'store' => self::CREATE,
        'add' => self::CREATE,
        'read' => self::READ,
        'index' => self::READ,
        'view' => self::READ,
        'list' => self::READ,
        'admin' => self::READ,
        'update' => self::UPDATE,
        'edit' => self::UPDATE,
        'delete' => self::DELETE,
        'destroy' => self::DELETE,
        'remove' => self::DELETE,
    );

    /**
     * Check if user has permission for a specific operation
     *
     * @param string $controller Controller/resource name (e.g., 'event', 'attendee')
     * @param string $operation Operation name (e.g., 'create', 'read', 'update', 'delete')
     * @return bool
     */
    public static function can($controller, $operation)
    {
        $permissions = AuthHandler::getPermissions();
        if (empty($permissions)) {
            return false;
        }

        // Check for full access wildcard "*"
        if (isset($permissions['*'])) {
            return true;
        }

        // Normalize controller name
        $controller = strtolower($controller);

        // Get permission string for this controller
        if (!isset($permissions[$controller])) {
            return false;
        }

        $permString = $permissions[$controller];

        // Check if controller has full access "*"
        if ($permString === '*' || $permString === '1 1 1 1') {
            return true;
        }

        $permArray = explode(' ', trim($permString));

        // Get operation index
        $operation = strtolower($operation);
        $index = isset(self::$operationMap[$operation]) ? self::$operationMap[$operation] : null;

        if ($index === null) {
            // Unknown operation, deny by default
            return false;
        }

        // Check permission
        return isset($permArray[$index]) && $permArray[$index] === '1';
    }

    /**
     * Check Create permission
     */
    public static function canCreate($controller)
    {
        return self::can($controller, 'create');
    }

    /**
     * Check Read permission
     */
    public static function canRead($controller)
    {
        return self::can($controller, 'read');
    }

    /**
     * Check Update permission
     */
    public static function canUpdate($controller)
    {
        return self::can($controller, 'update');
    }

    /**
     * Check Delete permission
     */
    public static function canDelete($controller)
    {
        return self::can($controller, 'delete');
    }

    /**
     * Get all permissions for current user
     * @return array
     */
    public static function getAllPermissions()
    {
        return AuthHandler::getPermissions();
    }

    /**
     * Get parsed permissions with readable format
     * @return array
     */
    public static function getParsedPermissions()
    {
        $permissions = AuthHandler::getPermissions();
        $result = array();

        foreach ($permissions as $controller => $permString) {
            // Handle full access "*" or "1 1 1 1"
            if ($permString === '*' || $permString === '1 1 1 1') {
                $result[$controller] = array(
                    'create' => true,
                    'read' => true,
                    'update' => true,
                    'delete' => true,
                    'raw' => $permString,
                );
                continue;
            }

            $permArray = explode(' ', trim($permString));
            $result[$controller] = array(
                'create' => isset($permArray[0]) && $permArray[0] === '1',
                'read' => isset($permArray[1]) && $permArray[1] === '1',
                'update' => isset($permArray[2]) && $permArray[2] === '1',
                'delete' => isset($permArray[3]) && $permArray[3] === '1',
                'raw' => $permString,
            );
        }

        return $result;
    }

    /**
     * Check permission and throw exception if denied
     * @throws CHttpException
     */
    public static function requirePermission($controller, $operation)
    {
        if (!self::can($controller, $operation)) {
            throw new CHttpException(403, 'Bạn không có quyền thực hiện thao tác này.');
        }
    }

    /**
     * Get menu permissions (array format with name, module, controller, action, root)
     * This is the raw permissions array used for building sidebar menu
     * @return array
     */
    public static function getMenuPermissions()
    {
        $session = Yii::app()->session;
        return isset($session['sso_menu_permissions']) ? $session['sso_menu_permissions'] : array();
    }

    /**
     * Set menu permissions from API response
     * @param array $permissions
     */
    public static function setMenuPermissions($permissions)
    {
        $session = Yii::app()->session;
        $session['sso_menu_permissions'] = $permissions;
    }
}
