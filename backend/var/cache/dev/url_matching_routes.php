<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/api/auth/logout' => [[['_route' => 'app_auth_logout__invoke', '_controller' => 'App\\Auth\\Controller\\LogoutController'], null, ['POST' => 0], null, false, false, null]],
        '/api/auth/refresh' => [[['_route' => 'app_auth_refreshtoken__invoke', '_controller' => 'App\\Auth\\Controller\\RefreshTokenController'], null, ['POST' => 0], null, false, false, null]],
        '/api/auth/register' => [[['_route' => 'app_auth_register__invoke', '_controller' => 'App\\Auth\\Controller\\RegisterController'], null, ['POST' => 0], null, false, false, null]],
        '/api/users' => [[['_route' => 'app_user_user_listusers', '_controller' => 'App\\User\\Controller\\UserController::listUsers'], null, ['GET' => 0], null, false, false, null]],
        '/api/user' => [
            [['_route' => 'app_user_user_getcurrentuser', '_controller' => 'App\\User\\Controller\\UserController::getCurrentUser'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_user_user_updatecurrentuser', '_controller' => 'App\\User\\Controller\\UserController::updateCurrentUser'], null, ['PATCH' => 0], null, false, false, null],
        ],
        '/api/tasks' => [
            [['_route' => 'app_task_task_list', '_controller' => 'App\\Task\\Controller\\TaskController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_task_task_create', '_controller' => 'App\\Task\\Controller\\TaskController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/categories' => [
            [['_route' => 'app_category_category_list', '_controller' => 'App\\Category\\Controller\\CategoryController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_category_category_create', '_controller' => 'App\\Category\\Controller\\CategoryController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/goals' => [
            [['_route' => 'app_goal_goal_list', '_controller' => 'App\\Goal\\Controller\\GoalController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_goal_goal_create', '_controller' => 'App\\Goal\\Controller\\GoalController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/habits' => [
            [['_route' => 'app_habit_habit_list', '_controller' => 'App\\Habit\\Controller\\HabitController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_habit_habit_create', '_controller' => 'App\\Habit\\Controller\\HabitController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/reminders' => [
            [['_route' => 'app_reminder_reminder_list', '_controller' => 'App\\Reminder\\Controller\\ReminderController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_reminder_reminder_create', '_controller' => 'App\\Reminder\\Controller\\ReminderController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/statistics' => [[['_route' => 'app_statistic_statistic_overview', '_controller' => 'App\\Statistic\\Controller\\StatisticController::overview'], null, ['GET' => 0], null, false, false, null]],
        '/api/finance-categories' => [
            [['_route' => 'app_financecategory_financecategory_list', '_controller' => 'App\\FinanceCategory\\Controller\\FinanceCategoryController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_financecategory_financecategory_create', '_controller' => 'App\\FinanceCategory\\Controller\\FinanceCategoryController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/finances' => [
            [['_route' => 'app_finance_finance_list', '_controller' => 'App\\Finance\\Controller\\FinanceController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_finance_finance_create', '_controller' => 'App\\Finance\\Controller\\FinanceController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/finance-plans' => [
            [['_route' => 'app_financeplan_financeplan_list', '_controller' => 'App\\FinancePlan\\Controller\\FinancePlanController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_financeplan_financeplan_create', '_controller' => 'App\\FinancePlan\\Controller\\FinancePlanController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/finance-plans/summary' => [[['_route' => 'app_financeplan_financeplan_summary', '_controller' => 'App\\FinancePlan\\Controller\\FinancePlanController::summary'], null, ['GET' => 0], null, false, false, null]],
        '/api/personal-state' => [[['_route' => 'app_personalstate_personalstate_show', '_controller' => 'App\\PersonalState\\Controller\\PersonalStateController::show'], null, ['GET' => 0], null, false, false, null]],
        '/api/health-metric-types' => [
            [['_route' => 'app_healthmetrictype_healthmetrictype_list', '_controller' => 'App\\HealthMetricType\\Controller\\HealthMetricTypeController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_healthmetrictype_healthmetrictype_create', '_controller' => 'App\\HealthMetricType\\Controller\\HealthMetricTypeController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/health-metrics' => [
            [['_route' => 'app_healthmetricentry_healthmetricentry_list', '_controller' => 'App\\HealthMetricEntry\\Controller\\HealthMetricEntryController::list'], null, ['GET' => 0], null, false, false, null],
            [['_route' => 'app_healthmetricentry_healthmetricentry_create', '_controller' => 'App\\HealthMetricEntry\\Controller\\HealthMetricEntryController::create'], null, ['POST' => 0], null, false, false, null],
        ],
        '/api/telegram/connect-link' => [[['_route' => 'app_telegram_telegram_connectlink', '_controller' => 'App\\Telegram\\Controller\\TelegramController::connectLink'], null, ['POST' => 0], null, false, false, null]],
        '/api/telegram/webhook' => [[['_route' => 'app_telegram_telegram_webhook', '_controller' => 'App\\Telegram\\Controller\\TelegramController::webhook'], null, ['POST' => 0], null, false, false, null]],
        '/api/telegram/auth' => [[['_route' => 'app_telegram_telegram_auth', '_controller' => 'App\\Telegram\\Controller\\TelegramController::auth'], null, ['POST' => 0], null, false, false, null]],
        '/api/telegram/config' => [[['_route' => 'app_telegram_telegram_config', '_controller' => 'App\\Telegram\\Controller\\TelegramController::config'], null, ['GET' => 0], null, false, false, null]],
        '/api/auth/login' => [[['_route' => 'login_check'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/_error/(\\d+)(?:\\.([^/]++))?(*:35)'
                .'|/api/(?'
                    .'|tasks/(\\d+)(?'
                        .'|(*:64)'
                    .')'
                    .'|goals/(\\d+)(*:83)'
                    .'|habits/(?'
                        .'|(\\d+)/log(*:109)'
                        .'|(\\d+)/logs(*:127)'
                        .'|(\\d+)/logs/skip(*:150)'
                        .'|(\\d+)/logs(*:168)'
                    .')'
                    .'|reminders/(\\d+)(?'
                        .'|(*:195)'
                    .')'
                    .'|finances/(\\d+)(*:218)'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        35 => [[['_route' => '_preview_error', '_controller' => 'error_controller::preview', '_format' => 'html'], ['code', '_format'], null, null, false, true, null]],
        64 => [
            [['_route' => 'app_task_task_show', '_controller' => 'App\\Task\\Controller\\TaskController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'app_task_task_update', '_controller' => 'App\\Task\\Controller\\TaskController::update'], ['id'], ['PATCH' => 0], null, false, true, null],
        ],
        83 => [[['_route' => 'app_goal_goal_update', '_controller' => 'App\\Goal\\Controller\\GoalController::update'], ['id'], ['PATCH' => 0], null, false, true, null]],
        109 => [[['_route' => 'app_habit_habit_addlog', '_controller' => 'App\\Habit\\Controller\\HabitController::addLog'], ['id'], ['POST' => 0], null, false, false, null]],
        127 => [[['_route' => 'app_habit_habit_getlogs', '_controller' => 'App\\Habit\\Controller\\HabitController::getLogs'], ['id'], ['GET' => 0], null, false, false, null]],
        150 => [[['_route' => 'app_habit_habit_skiplog', '_controller' => 'App\\Habit\\Controller\\HabitController::skipLog'], ['id'], ['POST' => 0], null, false, false, null]],
        168 => [[['_route' => 'app_habit_habit_deletelog', '_controller' => 'App\\Habit\\Controller\\HabitController::deleteLog'], ['id'], ['DELETE' => 0], null, false, false, null]],
        195 => [
            [['_route' => 'app_reminder_reminder_show', '_controller' => 'App\\Reminder\\Controller\\ReminderController::show'], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => 'app_reminder_reminder_update', '_controller' => 'App\\Reminder\\Controller\\ReminderController::update'], ['id'], ['PATCH' => 0], null, false, true, null],
            [['_route' => 'app_reminder_reminder_delete', '_controller' => 'App\\Reminder\\Controller\\ReminderController::delete'], ['id'], ['DELETE' => 0], null, false, true, null],
        ],
        218 => [
            [['_route' => 'app_finance_finance_update', '_controller' => 'App\\Finance\\Controller\\FinanceController::update'], ['id'], ['PATCH' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
