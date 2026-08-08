<?php
/** @var \App\View\AppView $this */
$req = $this->getRequest();
$ctrl = $req->getParam('controller');
function navClass($ctrl, $name) { return $ctrl === $name ? 'nav-link active' : 'nav-link'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · romahkawensatu</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <?= $this->Html->css('admin') ?>
</head>
<body>
<header class="site">
  <div class="head-inner">
    <?= $this->Html->link(
      '<span class="logo-slot"><img src="' . $this->Url->build('/img/logo.jpg') . '" alt="logo" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'"><span style="display:none">LOGO</span></span><span><span class="brand-name">romahkawensatu</span><br><span class="brand-sub">Admin</span></span>',
      ['controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Admin'],
      ['class' => 'brand', 'escape' => false]
    ) ?>
    <?= $this->Html->link('View site →', '/', ['class' => 'adminlink']) ?>
  </div>
</header>

<nav class="admin-nav">
  <div class="inner">
    <?= $this->Html->link('Dashboard', ['controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Admin'], ['class' => navClass($ctrl,'Dashboard')]) ?>
    <?= $this->Html->link('Bookings',  ['controller' => 'Bookings', 'action' => 'index', 'prefix' => 'Admin'], ['class' => navClass($ctrl,'Bookings')]) ?>
    <?= $this->Html->link('Studios',   ['controller' => 'Studios', 'action' => 'index', 'prefix' => 'Admin'], ['class' => navClass($ctrl,'Studios')]) ?>
    <?= $this->Html->link('Add-ons',   ['controller' => 'Addons', 'action' => 'index', 'prefix' => 'Admin'], ['class' => navClass($ctrl,'Addons')]) ?>
    <?= $this->Html->link('Gallery',   ['controller' => 'Galleries', 'action' => 'index', 'prefix' => 'Admin'], ['class' => navClass($ctrl,'Galleries')]) ?>
    <span class="spacer"></span>
    <?= $this->Html->link('Log out', ['controller' => 'Admins', 'action' => 'logout', 'prefix' => 'Admin']) ?>
  </div>
</nav>

<?= $this->Flash->render() ?>
<main><?= $this->fetch('content') ?></main>

<script>
document.querySelectorAll('.message').forEach(function(m){
  var ok = m.classList.contains('success');
  var pop = document.createElement('div');
  pop.className = 'flash-pop ' + (ok ? 'success' : 'error');
  pop.innerHTML = '<div class="box"><div class="ic">'+(ok?'\u2713':'!')+'</div>'+
    '<h3>'+(ok?'Success':'Notice')+'</h3><p>'+m.textContent+'</p><button class="btn">OK</button></div>';
  pop.querySelector('button').onclick = function(){ pop.remove(); };
  pop.onclick = function(e){ if(e.target===pop) pop.remove(); };
  document.body.appendChild(pop); m.remove();
});
</script>
<footer>
  <div class="footer-bottom" style="padding-top:0;border-top:none;max-width:1100px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div class="copyright">&copy; <?= date('Y') ?> romahkawensatu. All rights reserved.</div>
    <div class="legal-links">
      <a href="/">View Site</a>
      <a href="<?= $this->Url->build(['controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Admin']) ?>">Dashboard</a>
    </div>
  </div>
</footer>
</body>
</html>
