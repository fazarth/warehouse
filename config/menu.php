<?php

/**
 * Dynamic Menu Configuration
 * 
 * Each menu item can specify:
 * - title: Display name
 * - route: Laravel route name
 * - icon: Emoji or icon class
 * - module: Module code (null = always visible)
 * - roles: Array of allowed roles (['*'] = all roles)
 * - group: Menu group for categorization
 */

return [
    // ===== UTAMA =====
    [
        'title' => 'Dashboard',
        'route' => 'dashboard',
        'icon' => '📊',
        'module' => null,
        'roles' => ['*'],
        'group' => 'utama',
    ],
    
    // ===== DATA MASTER =====
    [
        'title' => 'Anggota',
        'route' => 'members.index',
        'icon' => '👥',
        'module' => 'member',
        'roles' => ['Super Admin', 'Admin Koperasi', 'Ketua', 'Bendahara', 'Petugas'],
        'group' => 'master',
    ],
    
    // ===== TRANSAKSI =====
    [
        'title' => 'Simpanan',
        'route' => 'savings.index',
        'icon' => '💰',
        'module' => 'saving',
        'roles' => ['Super Admin', 'Admin Koperasi', 'Bendahara', 'Petugas'],
        'group' => 'transaksi',
    ],
    [
        'title' => 'Pinjaman',
        'route' => 'loans.index',
        'icon' => '💳',
        'module' => 'loan',
        'roles' => ['Super Admin', 'Admin Koperasi', 'Ketua', 'Bendahara', 'Petugas'],
        'group' => 'transaksi',
    ],
    [
        'title' => 'Angsuran',
        'route' => 'installments.index',
        'icon' => '📅',
        'module' => 'loan',
        'roles' => ['Super Admin', 'Admin Koperasi', 'Bendahara', 'Petugas'],
        'group' => 'transaksi',
    ],
    
    // ===== KEUANGAN =====
    [
        'title' => 'Kas & Jurnal',
        'route' => 'finance.index',
        'icon' => '📒',
        'module' => 'finance',
        'roles' => ['Super Admin', 'Admin Koperasi', 'Bendahara'],
        'group' => 'keuangan',
    ],
    [
        'title' => 'SHU',
        'route' => 'shu.index',
        'icon' => '📈',
        'module' => 'shu',
        'roles' => ['Super Admin', 'Admin Koperasi', 'Ketua', 'Bendahara'],
        'group' => 'keuangan',
    ],
    
    // ===== UNIT USAHA =====
    [
        'title' => 'Inventori',
        'route' => 'inventory.index',
        'icon' => '📦',
        'module' => 'inventory',
        'roles' => ['Super Admin', 'Admin Koperasi', 'Petugas'],
        'group' => 'usaha',
    ],
    
    // ===== LAPORAN =====
    [
        'title' => 'Laporan',
        'route' => 'reports.index',
        'icon' => '📄',
        'module' => 'report',
        'roles' => ['Super Admin', 'Admin Koperasi', 'Ketua', 'Bendahara'],
        'group' => 'laporan',
    ],
    
    // ===== ADMIN =====
    [
        'title' => 'Audit Log',
        'route' => 'audit.index',
        'icon' => '🔍',
        'module' => null,
        'roles' => ['Super Admin', 'Admin Koperasi'],
        'group' => 'admin',
    ],
    [
        'title' => 'Kelola Modul',
        'route' => 'admin.modules.index',
        'icon' => '🧩',
        'module' => null,
        'roles' => ['Super Admin'],
        'group' => 'admin',
    ],
    [
        'title' => 'Kelola User',
        'route' => 'admin.users.index',
        'icon' => '👤',
        'module' => null,
        'roles' => ['Super Admin', 'Admin Koperasi'],
        'group' => 'admin',
    ],
    [
        'title' => 'Pengaturan',
        'route' => 'settings.index',
        'icon' => '⚙️',
        'module' => 'setting',
        'roles' => ['*'],
        'group' => 'admin',
    ],
];
