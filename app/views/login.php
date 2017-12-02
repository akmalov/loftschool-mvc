<h1>Авторизация</h1>
<span style="color: red"><?= $data['message'] ?></span>
<br>
<form action="/login/authorization/" method="post">
  <label>Логин</label>
  <input name="login">
  <br><br>
  <label>Пароль</label>
  <input name="password">
  <br><br>
  <input type="submit">
</form>