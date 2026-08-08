<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    /* ---------- CUSTOMER AREA (no login) ---------- */
    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Bookings', 'action' => 'step1']);
        $builder->connect('/studios', ['controller' => 'Studios', 'action' => 'index']);
        $builder->connect('/studios/{id}', ['controller' => 'Studios', 'action' => 'view'])
                ->setPatterns(['id' => '\d+'])->setPass(['id']);
        $builder->connect('/about', ['controller' => 'Pages', 'action' => 'display', 'about']);

        // Multi-step booking flow
        $builder->connect('/book', ['controller' => 'Bookings', 'action' => 'step1']);
        $builder->connect('/book/step1', ['controller' => 'Bookings', 'action' => 'step1']);
        $builder->connect('/book/save-step1', ['controller' => 'Bookings', 'action' => 'saveStep1']);
        $builder->connect('/book/step2', ['controller' => 'Bookings', 'action' => 'step2']);
        $builder->connect('/book/save-step2', ['controller' => 'Bookings', 'action' => 'saveStep2']);
        $builder->connect('/book/step3', ['controller' => 'Bookings', 'action' => 'step3']);
        $builder->connect('/book/save-step3', ['controller' => 'Bookings', 'action' => 'saveStep3']);
        $builder->connect('/book/step4', ['controller' => 'Bookings', 'action' => 'step4']);
        $builder->connect('/book/process-payment', ['controller' => 'Bookings', 'action' => 'processPayment']);

        // ToyyibPay callback & success
        $builder->connect('/bookings/callback', ['controller' => 'Bookings', 'action' => 'callback']);
        $builder->connect('/bookings/success/{bookingId}', ['controller' => 'Bookings', 'action' => 'success'])
                ->setPatterns(['bookingId' => '\d+'])->setPass(['bookingId']);

        // JSON feed for the calendar (confirmed bookings in a month)
        $builder->connect('/bookings/calendar-data', ['controller' => 'Bookings', 'action' => 'calendarData']);
        $builder->fallbacks();
    });

    /* ---------- ADMIN AREA (login required, enforced server-side) ----------
       The fallbacks() below auto-route the CRUD controllers:
         /admin/studios, /admin/addons, /admin/galleries, /admin/bookings  */
    $routes->prefix('Admin', function (RouteBuilder $builder): void {
        $builder->connect('/', ['controller' => 'Dashboard', 'action' => 'index']);
        $builder->connect('/login', ['controller' => 'Admins', 'action' => 'login']);
        $builder->connect('/logout', ['controller' => 'Admins', 'action' => 'logout']);
        $builder->fallbacks();
    });
};
