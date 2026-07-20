<?php

/**
 * Package → module allow-list for LicenseService.
 * Enterprise WMS portfolio default: LICENSE_TYPE=full (see .env / config/license.php).
 */
return [
    'basic' => [
        'name' => 'Paket Basic',
        'description' => 'Untuk gudang kecil dengan fitur dasar',
        'modules' => [
            'warehouse', 'location', 'product', 'supplier',
            'purchase_order', 'goods_receipt', 'putaway', 'goods_issue',
            'inventory', 'mutation', 'report', 'setting',
        ],
        'max_members' => null,
        'features' => [
            'Penerimaan barang',
            'Pengiriman barang',
            'Stok overview',
            'Basic reporting',
        ],
        'price' => 'Rp 1.500.000',
    ],

    'pro' => [
        'name' => 'Paket Pro',
        'description' => 'Untuk gudang menengah dengan fitur lengkap',
        'modules' => [
            'warehouse', 'location', 'product', 'supplier',
            'purchase_order', 'goods_receipt', 'putaway', 'goods_issue',
            'stockopname', 'return', 'cycle_count',
            'inventory', 'mutation', 'report', 'setting',
        ],
        'max_members' => null,
        'features' => [
            'Semua fitur Basic',
            'Stock Opname',
            'Returns & Cycle Count',
            'Mutasi stok',
            'Advanced reporting',
        ],
        'price' => 'Rp 3.000.000',
    ],

    'full' => [
        'name' => 'Paket Full / Enterprise',
        'description' => 'Untuk gudang besar dengan semua fitur',
        'modules' => ['*'],
        'max_members' => null,
        'features' => [
            'Semua fitur Pro',
            'Multi warehouse',
            'Batch/Lot tracking',
            'Expiry date management',
            'REST API access',
            'Priority support',
        ],
        'price' => 'Rp 5.000.000',
    ],
];
