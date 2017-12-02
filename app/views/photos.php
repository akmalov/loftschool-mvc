<style>
  figure {
    float: left;
    border: solid grey;
  }
</style>
<h1>Кликните на картинку чтобы заменить её на другую</h1>
<p>
  <a href="/">На главную страницу</a>
</p>
<?php foreach ($data as $item) : ?>
  <a href="/files/change/<?= $item['id'] ?>">
    <figure>
      <figcaption><?= $item['name'] ?></figcaption>
      <br>
      <img src="/photos/<?= $item['photo'] ?>" height="200">
    </figure>
  </a>
<? endforeach; ?>
