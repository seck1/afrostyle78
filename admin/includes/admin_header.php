<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? htmlspecialchars($adminTitle) . ' — ' : '' ?>Admin AfroStyle</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
    <style>
        :root{--gold:#C8921A;--dark:#0E0A06;--dark2:#1A1208;--dark3:#2C1E0F;--cream:#FDF6EC;--cream2:#F5E9D4;--text:#1A1208;--muted:#7A6248;--sidebar:290px;}
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Syne',sans-serif;background:#F0EBE3;color:var(--text);display:flex;min-height:100vh;}
        /* SIDEBAR */
        .sidebar{width:var(--sidebar);background:var(--dark);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;z-index:100;}
        .sidebar-brand{padding:28px 24px;border-bottom:1px solid rgba(200,146,26,0.15);}
        .sidebar-brand img{height:40px;opacity:0.9;}
        .sidebar-brand span{display:block;font-size:1rem;color:rgba(253,246,236,0.7);letter-spacing:0.12em;text-transform:uppercase;margin-top:8px;}
        .sidebar-nav{flex:1;padding:20px 0;}
        .nav-section-title{font-size:0.85rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;color:var(--gold);padding:20px 24px 10px;}
        .sidebar-link{display:flex;align-items:center;gap:14px;padding:15px 24px;color:#fff;text-decoration:none;font-size:1.05rem;font-weight:600;letter-spacing:0.02em;transition:all 0.25s;border-left:3px solid transparent;}
        .sidebar-link:hover{color:var(--gold);background:rgba(200,146,26,0.1);}
        .sidebar-link.active{color:var(--gold);background:rgba(200,146,26,0.15);border-left-color:var(--gold);}
        .sidebar-link svg{width:20px;height:20px;flex-shrink:0;}
        .badge{background:var(--gold);color:var(--dark);font-size:0.8rem;font-weight:700;padding:3px 9px;border-radius:2px;margin-left:auto;}
        .sidebar-footer{padding:20px 24px;border-top:1px solid rgba(200,146,26,0.1);}
        .sidebar-footer a{color:rgba(253,246,236,0.6);font-size:1rem;text-decoration:none;transition:color 0.25s;}
        .sidebar-footer a:hover{color:var(--gold);}
        /* MAIN */
        .admin-main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column;min-height:100vh;}
        .admin-topbar{background:#fff;padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #E8E0D8;position:sticky;top:0;z-index:50;}
        .admin-topbar h1{font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:400;color:var(--dark);}
        .topbar-right{display:flex;align-items:center;gap:16px;}
        .topbar-user{display:flex;align-items:center;gap:10px;font-size:0.82rem;color:var(--muted);}
        .topbar-user strong{color:var(--dark);}
        .logout-btn{background:none;border:1px solid #E8E0D8;padding:7px 16px;font-family:'Syne',sans-serif;font-size:0.8rem;cursor:pointer;color:var(--muted);text-decoration:none;transition:all 0.25s;}
        .logout-btn:hover{border-color:var(--gold);color:var(--gold);}
        .admin-content{flex:1;padding:32px;font-size:0.9rem;}
        /* CARDS */
        .stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;}
        .stat-card{background:#fff;padding:24px;border-top:3px solid var(--gold);}
        .stat-icon{font-size:1.4rem;margin-bottom:12px;}
        .stat-value{font-size:1.8rem;font-weight:800;color:var(--dark);line-height:1;}
        .stat-label{font-size:0.72rem;color:var(--muted);letter-spacing:0.1em;text-transform:uppercase;margin-top:4px;}
        /* TABLE */
        .admin-table{width:100%;border-collapse:collapse;background:#fff;}
        .admin-table th{font-size:0.9rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);padding:15px 18px;text-align:left;border-bottom:2px solid #F0EBE3;}
        .admin-table td{padding:16px 18px;border-bottom:1px solid #F0EBE3;vertical-align:middle;font-size:1.15rem;}
        .admin-table tr:hover td{background:#FDFAF6;}
        /* STATUS BADGES */
        .status-badge{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;font-size:0.72rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;}
        .status-pending{background:rgba(200,146,26,0.12);color:#8B6914;}
        .status-confirmed{background:rgba(33,150,243,0.1);color:#1565C0;}
        .status-in_production{background:rgba(156,39,176,0.1);color:#7B1FA2;}
        .status-shipped{background:rgba(255,152,0,0.12);color:#E65100;}
        .status-delivered{background:rgba(26,122,74,0.1);color:#1A7A4A;}
        .status-cancelled{background:rgba(192,57,43,0.1);color:#C0392B;}
        /* FORMS */
        .admin-form .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;}
        .admin-form .form-row.full{grid-template-columns:1fr;}
        .admin-form label{display:block;font-size:1.1rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted);margin-bottom:10px;}
        .admin-form input,.admin-form select,.admin-form textarea{width:100%;padding:15px 18px;border:1.5px solid #E8E0D8;background:#fff;font-family:'Syne',sans-serif;font-size:1.15rem;outline:none;transition:border-color 0.25s;color:var(--dark);}
        .admin-form input:focus,.admin-form select:focus,.admin-form textarea:focus{border-color:var(--gold);}
        .admin-card-title{font-size:1.4rem;font-weight:700;color:var(--dark);}
        /* BTNS */
        .btn-admin{display:inline-flex;align-items:center;gap:8px;padding:13px 28px;font-family:'Syne',sans-serif;font-size:1rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;cursor:pointer;border:none;text-decoration:none;transition:all 0.25s;}
        .btn-gold{background:var(--gold);color:var(--dark);}
        .btn-gold:hover{background:#E8B84B;}
        .btn-dark{background:var(--dark);color:var(--cream);}
        .btn-dark:hover{background:var(--dark3);}
        .btn-danger{background:#C0392B;color:#fff;}
        .btn-danger:hover{background:#E74C3C;}
        .btn-outline{background:transparent;border:1px solid #E8E0D8;color:var(--dark);}
        .btn-outline:hover{border-color:var(--gold);color:var(--gold);}
        .btn-sm{padding:6px 14px;font-size:0.65rem;}
        /* CARD */
        .admin-card{background:#fff;padding:24px;margin-bottom:24px;}
        .admin-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #F0EBE3;}
        .admin-card-title{font-size:0.78rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--dark);}
        /* ALERT */
        .alert{padding:12px 18px;margin-bottom:20px;font-size:0.82rem;border-left:3px solid;}
        .alert-success{background:rgba(26,122,74,0.08);border-color:#1A7A4A;color:#1A7A4A;}
        .alert-error{background:rgba(192,57,43,0.08);border-color:#C0392B;color:#C0392B;}
        .alert-info{background:rgba(200,146,26,0.08);border-color:var(--gold);color:#8B6914;}
        @media(max-width:1024px){.stat-grid{grid-template-columns:1fr 1fr;}}
        @media(max-width:768px){.sidebar{transform:translateX(-100%);}.admin-main{margin-left:0;}}
    </style>
    <?= isset($adminHead) ? $adminHead : '' ?>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="<?= SITE_URL ?>/logo.jpg" alt="AfroStyle">
        <span>Administration</span>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Principal</div>
        <a href="<?= ADMIN_URL ?>/index.php" class="sidebar-link <?= $currentPage==='index'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Tableau de bord
        </a>

        <div class="nav-section-title">Commandes</div>
        <a href="<?= ADMIN_URL ?>/commandes.php" class="sidebar-link <?= $currentPage==='commandes'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Toutes les commandes
            <?php
            $pendingCount = getDB()->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
            if($pendingCount > 0): ?>
            <span class="badge"><?= $pendingCount ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= ADMIN_URL ?>/livraisons.php" class="sidebar-link <?= $currentPage==='livraisons'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
            Suivi livraisons
        </a>
        <a href="<?= ADMIN_URL ?>/livraison-tarifs.php" class="sidebar-link <?= $currentPage==='livraison-tarifs'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Tarifs livraison
        </a>

        <div class="nav-section-title">Catalogue</div>
        <a href="<?= ADMIN_URL ?>/produits.php" class="sidebar-link <?= $currentPage==='produits'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Produits
        </a>
        <a href="<?= ADMIN_URL ?>/categories.php" class="sidebar-link <?= $currentPage==='categories'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            Catégories
        </a>

        <div class="nav-section-title">Clients</div>
        <a href="<?= ADMIN_URL ?>/clients.php" class="sidebar-link <?= $currentPage==='clients'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            Clients
        </a>

        <div class="nav-section-title">Configuration</div>
        <a href="<?= ADMIN_URL ?>/parametres.php" class="sidebar-link <?= $currentPage==='parametres'?'active':'' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            Paramètres
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= SITE_URL ?>" target="_blank">← Voir la boutique</a>&nbsp;&nbsp;
        <a href="logout.php">Déconnexion</a>
    </div>
</aside>

<div class="admin-main">
    <div class="admin-topbar">
        <h1><?= isset($adminTitle) ? htmlspecialchars($adminTitle) : 'Dashboard' ?></h1>
        <div class="topbar-right">
            <div class="topbar-user">Connecté : <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? '') ?></strong></div>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </div>
    <div class="admin-content">
