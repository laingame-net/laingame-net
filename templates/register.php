<?php $this->init_page(); // we must call this function at the begining ?>
<br>
<form action="/site/register" method="post">
  <div class="container">
    <label for="username"><b>Username</b></label>
    <input type="text" placeholder="Enter Username" name="username" value="<?=$last_post['username']?>" required>

    <label for="email"><b>E-mail</b></label>
    <input type="text" placeholder="Enter E-mail" name="email" value="<?=$last_post['email']?>" required>

    <label for="password1"><b>Password</b></label>
    <input type="password" placeholder="Enter Password" name="password1" required>

    <label for="password2"><b>Confirm password</b></label>
    <input type="password" placeholder="Enter Password" name="password2" required>

    <div>
      <label>
        Remember me
        <input type="checkbox" checked="checked" name="remember">
      </label>
    </div>
    <button type="submit">Register</button>
    <?=$error ? '<div>'.$error.'</div>' : ''?>
  </div>
</form>
