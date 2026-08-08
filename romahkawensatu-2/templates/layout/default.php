<?php /** @var \App\View\AppView $this */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>romahkawensatu<?= $this->fetch('title') ? ' · ' . $this->fetch('title') : '' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;1,9..144,400&family=Hanken+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <?= $this->Html->css('app') ?>
</head>
<body>
<header class="site">
  <div class="head-inner">
    <a class="brand" href="/" style="text-decoration:none">
      <!-- Drop logo at webroot/img/logo.jpg -->
      <span class="logo-slot">
        <img src="<?= $this->Url->build('/img/logo.jpg') ?>" alt="logo" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <span style="display:none">LOGO</span>
      </span>
      <span>
        <span class="brand-name">romahkawensatu</span><br>
        <span class="brand-sub">Studio Rental</span>
      </span>
    </a>
    <?php if (!$this->getRequest()->getParam('prefix')): ?>
    <a class="adminlink" href="<?= $this->Url->build('/') ?>">Home</a>
    <a class="adminlink" href="<?= $this->Url->build('/studios') ?>">Studios</a>
    <a class="adminlink" href="<?= $this->Url->build('/galleries') ?>">Gallery</a>
    <a class="adminlink" href="<?= $this->Url->build('/about') ?>">About Us</a>
    <a class="adminlink" href="<?= $this->Url->build('/book') ?>">Book Now</a>
    <?php endif; ?>
  </div>
</header>

<?= $this->Flash->render() ?>

<main><?= $this->fetch('content') ?></main>

<footer class="site-footer">
  <div class="footer-top">
    <!-- Brand -->
    <div class="brand-col">
      <div class="brand-name">romahkawensatu</div>
      <div class="tagline">Premium studio rental spaces for your creative projects. Capture your vision in our uniquely themed studios.</div>
      <div class="social-row">
        <a href="#" class="social-icon" title="Google">G</a>
        <a href="#" class="social-icon" title="Facebook">f</a>
        <a href="#" class="social-icon" title="TikTok">tt</a>
      </div>
    </div>

    <!-- Quick Links -->
    <div class="links-col">
      <div class="col-title">Quick Links</div>
      <ul class="footer-links">
        <li><a href="<?= $this->Url->build('/') ?>">Home</a></li>
        <li><a href="<?= $this->Url->build('/studios') ?>">Studios</a></li>
        <li><a href="<?= $this->Url->build('/galleries') ?>">Gallery</a></li>
        <li><a href="<?= $this->Url->build('/about') ?>">About Us</a></li>
        <li><a href="<?= $this->Url->build('/book') ?>">Book Now</a></li>
      </ul>
    </div>

    <!-- Contact -->
    <div class="contact-col">
      <div class="col-title">Contact</div>
      <div class="contact-line"><span>Phone</span>+60 12-345 6789</div>
      <div class="contact-line"><span>Email</span>hello@romahkawensatu.my</div>
      <div class="contact-line"><span>Location</span>Kuala Lumpur, MY</div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="copyright">&copy; <?= date('Y') ?> romahkawensatu. All rights reserved.</div>
    <div class="legal-links">
      <a href="#">Privacy Policy</a>
      <a href="#">Terms of Service</a>
    </div>
  </div>
</footer>

<script>
// Turn CakePHP flash messages into a centered popup (matches the prototype).
document.querySelectorAll('.message').forEach(function(m){
  var ok = m.classList.contains('success');
  var pop = document.createElement('div');
  pop.className = 'flash-pop ' + (ok ? 'success' : 'error');
  pop.innerHTML = '<div class="box"><div class="ic">'+(ok?'\u2713':'!')+'</div>'+
    '<h3>'+(ok?'Success':'Notice')+'</h3><p>'+m.textContent+'</p>'+
    '<button class="btn">OK</button></div>';
  pop.querySelector('button').onclick = function(){ pop.remove(); };
  pop.onclick = function(e){ if(e.target===pop) pop.remove(); };
  document.body.appendChild(pop);
  m.remove();
});
</script>
</body>
</html>
