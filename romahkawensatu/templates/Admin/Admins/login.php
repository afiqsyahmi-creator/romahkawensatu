<div class="login-card">
  <div class="eyebrow">Admin access</div>
  <h1 class="h">Sign <em>in</em></h1>
  <?php $req = $this->getRequest(); ?>
  <?= $this->Form->create() ?>
    <label>Email</label><?= $this->Form->control('email', ['type' => 'email', 'label' => false, 'required' => true]) ?>
    <label>Password</label><?= $this->Form->control('password', ['type' => 'password', 'label' => false, 'required' => true]) ?>
    <?= $this->Form->button('Log in', ['class' => 'btn', 'style' => 'width:100%']) ?>
  <?= $this->Form->end() ?>
</div>
