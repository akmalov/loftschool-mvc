<h1>Изменение фотографии пользователя</h1>
<h2>Имя: <?= $data['name'] ?></h2>
<p>Старая фотография</p>
<img src="/photos/<?= $data['photo'] ?>" alt="аватар">
<form action="/files/change/<?= $data['id'] ?>/" method="post" enctype="multipart/form-data">
  <div class="field">
    <label>Новая фотография</label>
    <input name="photo" type="file">
  </div>
  <div class="field">
    <input type="submit" value="Изменить">
  </div>
</form>
