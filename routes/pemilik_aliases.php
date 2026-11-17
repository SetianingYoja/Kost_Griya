
// Backward compatibility alias pemilik.* -> pengelola.* (hanya untuk URL generation)
// Inject ke RouteCollection.nameList setelah semua routes terdaftar
(function () {
    $routes = app('router')->getRoutes();
    $ref = new ReflectionClass($routes);
    $prop = $ref->getProperty('nameList');
    $prop->setAccessible(true);
    $nameList = $prop->getValue($routes);

    $aliases = [
        'pemilik.dashboard'            => 'pengelola.dashboard',
        'pemilik.kamar.index'          => 'pengelola.kamar.index',
        'pemilik.kamar.create'         => 'pengelola.kamar.create',
        'pemilik.kamar.store'          => 'pengelola.kamar.store',
        'pemilik.kamar.show'           => 'pengelola.kamar.show',
        'pemilik.kamar.edit'           => 'pengelola.kamar.edit',
        'pemilik.kamar.update'         => 'pengelola.kamar.update',
        'pemilik.kamar.destroy'        => 'pengelola.kamar.destroy',
        'pemilik.booking.index'        => 'pengelola.booking.index',
        'pemilik.booking.show'         => 'pengelola.booking.show',
        'pemilik.booking.approve'      => 'pengelola.booking.approve',
        'pemilik.booking.reject'       => 'pengelola.booking.reject',
        'pemilik.pembayaran.index'     => 'pengelola.pembayaran.index',
        'pemilik.pembayaran.show'      => 'pengelola.pembayaran.show',
        'pemilik.pembayaran.approve'   => 'pengelola.pembayaran.approve',
        'pemilik.pembayaran.reject'    => 'pengelola.pembayaran.reject',
        'pemilik.penghuni.index'       => 'pengelola.penghuni.index',
        'pemilik.penghuni.show'        => 'pengelola.penghuni.show',
        'pemilik.tagihan.index'        => 'pengelola.tagihan.index',
        'pemilik.tagihan.create'       => 'pengelola.tagihan.create',
        'pemilik.tagihan.store'        => 'pengelola.tagihan.store',
        'pemilik.tagihan.show'         => 'pengelola.tagihan.show',
        'pemilik.perpanjangan.index'   => 'pengelola.perpanjangan.index',
        'pemilik.perpanjangan.show'    => 'pengelola.perpanjangan.show',
        'pemilik.perpanjangan.approve' => 'pengelola.perpanjangan.approve',
        'pemilik.perpanjangan.reject'  => 'pengelola.perpanjangan.reject',
        'pemilik.keluhan.index'        => 'pengelola.keluhan.index',
        'pemilik.keluhan.show'         => 'pengelola.keluhan.show',
        'pemilik.keluhan.update'       => 'pengelola.keluhan.update',
        'pemilik.rating.index'         => 'pengelola.rating.index',
        'pemilik.rating.approve'       => 'pengelola.rating.approve',
        'pemilik.rating.reject'        => 'pengelola.rating.reject',
        'pemilik.rating.balas'         => 'pengelola.rating.balas',
        'pemilik.rating.bulk'          => 'pengelola.rating.bulk',
        'pemilik.rating.approve-all'   => 'pengelola.rating.approve-all',
        'pemilik.laporan.index'        => 'pengelola.laporan.index',
    ];

    foreach ($aliases as $alias => $target) {
        if (isset($nameList[$target])) {
            $nameList[$alias] = $nameList[$target];
        }
    }

    $prop->setValue($routes, $nameList);
})();
