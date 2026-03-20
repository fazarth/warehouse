<?php

return [
    'basic' => [
        'name' => 'Paket Basic',
        'description' => 'Untuk gudang kecil dengan fitur dasar',
        'modules' => ['warehouse', 'product', 'supplier', 'goods_receipt', 'goods_issue', 'inventory', 'report', 'setting'],
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
        'modules' => ['warehouse', 'product', 'supplier', 'goods_receipt', 'goods_issue', 'stockopname', 'inventory', 'report', 'setting'],
        'max_members' => null,
        'features' => [
            'Semua fitur Basic',
            'Stock Opname',
            'Mutasi antar gudang',
            'Stock card tracking',
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
