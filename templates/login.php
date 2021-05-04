<?php $this->init_page(); // we must call this function at the begining ?>
<br>
 <form action="login" method="post">
  <div class="container">
    <label for="username"><b>Username</b></label>
    <input type="text" placeholder="Enter Username" name="username" value="<?=@$last_username ? $last_username : ''?>" required>

    <label for="password"><b>Password</b></label>
    <input type="password" placeholder="Enter Password" name="password" required>

    <div>
      <label>
        Remember me
        <input type="checkbox" checked="checked" name="remember">
      </label>
    </div>
    <button type="submit">Login</button>
  </div>
  <div><?=$error ?? ""?></div>
</form>
