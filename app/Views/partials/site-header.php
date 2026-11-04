<?php $siteBase = (string) ($baseUrl ?? '/'); $activePage = (string) ($activePage ?? 'home'); ?>
<header class="site-header"><div class="container header-wrap">
<a class="brand" href="<?= \Noor\Core\View::escape($siteBase) ?>" aria-label="Noor home"><img src="<?= \Noor\Core\View::escape($siteBase) ?>assets/img/noor-mark.png" alt="" width="36" height="36"><span>Noor<span class="brand-suffix"> / نور</span></span></a>
<button class="menu-toggle" type="button" aria-controls="main-nav" aria-expanded="false" aria-label="باز کردن منو">☰</button>
<nav class="main-nav" id="main-nav" aria-label="منوی اصلی">
<a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="<?= \Noor\Core\View::escape($siteBase) ?>">خانه</a>
<a class="<?= $activePage === 'docs' ? 'active' : '' ?>" href="<?= \Noor\Core\View::escape($siteBase) ?>docs">مستندات</a>
<a href="<?= \Noor\Core\View::escape($siteBase) ?>docs/quick-start">شروع سریع</a>
<a href="<?= \Noor\Core\View::escape($siteBase) ?>docs/architecture">معماری</a>
<a href="<?= \Noor\Core\View::escape($siteBase) ?>docs/api">مرجع API</a>
<a href="<?= \Noor\Core\View::escape($siteBase) ?>docs/testing">تست‌ها</a>
</nav><span class="version-pill">v1.0.0-stable</span></div></header>
