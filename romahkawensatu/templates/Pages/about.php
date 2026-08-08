<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'About Us');
?>
<style>
.about-hero {
  text-align: center;
  padding: 60px 28px 40px;
  max-width: 720px;
  margin: 0 auto;
}
.about-hero .eyebrow {
  font-family: "Space Mono", monospace;
  font-size: 10px;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--muted-text);
  margin-bottom: 12px;
}
.about-hero h1 {
  font-family: "Cormorant Garamond", serif;
  font-size: clamp(36px, 5vw, 52px);
  font-weight: 700;
  letter-spacing: -.02em;
  color: var(--charcoal);
  margin-bottom: 16px;
}
.about-hero h1 em {
  font-style: italic;
  color: var(--gold);
  font-weight: 400;
}
.about-hero p {
  font-family: "Jost", sans-serif;
  font-size: 16px;
  line-height: 1.7;
  color: var(--muted-text);
}
.about-section {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 28px 48px;
}
.about-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-bottom: 48px;
}
@media (max-width: 700px) {
  .about-grid { grid-template-columns: 1fr; }
}
.about-card {
  background: linear-gradient(145deg, #fcf9f2, #f7f0e4);
  border: 1px solid #d4c5a8;
  border-radius: var(--radius);
  padding: 28px 24px;
  box-shadow: 0 4px 20px rgba(201,169,108,0.08);
  transition: box-shadow .25s, border-color .25s;
}
.about-card:hover {
  box-shadow: 0 8px 28px rgba(201,169,108,0.15);
  border-color: #c9a96e;
}
.about-card .icon {
  font-size: 28px;
  margin-bottom: 12px;
}
.about-card h3 {
  font-family: "Cormorant Garamond", serif;
  font-size: 20px;
  font-weight: 600;
  color: var(--charcoal);
  margin-bottom: 8px;
}
.about-card p {
  font-family: "Jost", sans-serif;
  font-size: 14px;
  line-height: 1.6;
  color: var(--muted-text);
}
.about-studios {
  margin-top: 16px;
}
.about-studios h2 {
  font-family: "Cormorant Garamond", serif;
  font-size: 26px;
  font-weight: 700;
  text-align: center;
  color: var(--charcoal);
  margin-bottom: 24px;
}
.about-studios h2 em {
  font-style: italic;
  color: var(--gold);
  font-weight: 400;
}
.studio-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
}
.studio-tag {
  font-family: "Jost", sans-serif;
  font-size: 13px;
  padding: 8px 18px;
  border-radius: 20px;
  background: linear-gradient(145deg, #fcf9f2, #f7f0e4);
  border: 1px solid #d4c5a8;
  color: #a8894e;
  text-decoration: none;
  transition: all .2s;
}
.studio-tag:hover {
  background: #c9a96e;
  border-color: #c9a96e;
  color: #fff;
}
.about-contact {
  text-align: center;
  margin-top: 48px;
  padding: 32px;
  background: var(--card-bg);
  border: 1px solid var(--line);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
}
.about-contact h3 {
  font-family: "Cormorant Garamond", serif;
  font-size: 22px;
  font-weight: 600;
  color: var(--charcoal);
  margin-bottom: 12px;
}
.about-contact p {
  font-family: "Jost", sans-serif;
  font-size: 14px;
  color: var(--muted-text);
  margin-bottom: 4px;
}
</style>

<div class="about-hero">
  <p class="eyebrow">About Us</p>
  <h1>About <em>romahkawensatu</em></h1>
  <p>Premium studio rental spaces designed for creative professionals, content creators, and anyone looking for a unique backdrop to bring their vision to life.</p>
</div>

<div class="about-section">
  <div class="about-grid">
    <div class="about-card">
      <div class="icon">🎯</div>
      <h3>Our Mission</h3>
      <p>To provide accessible, beautifully themed studio spaces that inspire creativity and make professional-quality content creation available to everyone.</p>
    </div>
    <div class="about-card">
      <div class="icon">✨</div>
      <h3>Our Vision</h3>
      <p>To be Malaysia's premier creative studio destination, known for our unique themed environments, exceptional service, and commitment to fostering creative expression.</p>
    </div>
    <div class="about-card">
      <div class="icon">🏠</div>
      <h3>Our Spaces</h3>
      <p>Each studio is carefully curated with distinct themes — from classic library charm to bohemian warmth — ensuring every photoshoot or video project has the perfect setting.</p>
    </div>
    <div class="about-card">
      <div class="icon">📍</div>
      <h3>Our Location</h3>
      <p>Conveniently located in Kuala Lumpur, our studios are easily accessible and equipped with everything you need for a seamless creative session.</p>
    </div>
  </div>

  <div class="about-studios">
    <h2>Explore Our <em>Studios</em></h2>
    <div class="studio-tags">
      <a href="<?= $this->Url->build('/galleries') ?>?studio=Harry+Potter+Library" class="studio-tag">Harry Potter Library</a>
      <a href="<?= $this->Url->build('/galleries') ?>?studio=Rattan+Studio" class="studio-tag">Rattan Studio</a>
      <a href="<?= $this->Url->build('/galleries') ?>?studio=Firepit+Studio" class="studio-tag">Firepit Studio</a>
      <a href="<?= $this->Url->build('/galleries') ?>?studio=Retro+Cafe+Studio" class="studio-tag">Retro Cafe Studio</a>
      <a href="<?= $this->Url->build('/galleries') ?>?studio=Bohemian+Studio" class="studio-tag">Bohemian Studio</a>
      <a href="<?= $this->Url->build('/galleries') ?>?studio=Barber+Studio" class="studio-tag">Barber Studio</a>
      <a href="<?= $this->Url->build('/galleries') ?>?studio=Muji+Studio" class="studio-tag">Muji Studio</a>
    </div>
  </div>
</div>
