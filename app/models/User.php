<?php

namespace Final2\Models;

use PDO;

class User extends Model
{
    public $message;
    public $success;
    public $table;
    public $photos;

    private function hash(string $password): string
    {
        return password_hash(
            $password,
            PASSWORD_BCRYPT,
            ['salt' => '!@#$^%jl2agj5l7hAUVCa8dsg']
        );
    }

    private function checkImage(array $photo, string &$extension): bool
    {
        if (is_null($photo) || $photo['name'] === '') {
            return false;
        }
        $acceptableExtensions = ['bmp', 'gif', 'jpg', 'png', 'svg'];
        $maxFileSize = 5;
        $tmp_name = $photo['tmp_name'];
        $extension = preg_replace('/.*\./', '', $photo['name']);
        $extension = strtolower($extension);
        if (!in_array($extension, $acceptableExtensions)) {
            $this->success = false;
            $this->message = 'Неверное расширение файла';
            return false;
        }
        $type = mime_content_type($tmp_name);
        if (substr($type, 0, 5) !== 'image') {
            $this->success = false;
            $this->message = 'Выбранный файл не является изображением';
            return false;
        }
        if (filesize($tmp_name) > $maxFileSize * 1024 ** 3) {
            $this->success = false;
            $this->message = 'Размер файла - не более ' . $maxFileSize . 'МБ';
            return false;
        }
        return true;
    }

    public function registerNewUser(
        string $login,
        string $password1,
        string $password2,
        string $name,
        string $age,
        string $description,
        array $photo
    ) {
        $query = 'SELECT COUNT(*) FROM users WHERE login = ?';
        $stmt = $this->dbh->prepare($query);
        $stmt->execute([$login]);
        $count = $stmt->fetch(PDO::FETCH_NUM)[0];
        if ($count[0] !== '0') {
            $this->success = false;
            $this->message = 'Имя пользователя уже существует';
            return;
        }

        if ($password1 !== $password2) {
            $this->success = false;
            $this->message = 'Пароли не совпадают';
            return;
        }

        $extension = '';
        $imageSent = $this->checkImage($photo, $extension);

        $query = 'INSERT INTO users (login, password, name, age, description)' .
            'VALUES(?, ?, ?, ?, ?);';
        $values = [
            strip_tags($login),
            $this->hash($password1),
            strip_tags($name),
            filter_var($age, FILTER_VALIDATE_INT),
            htmlspecialchars($description)
        ];
        $this->dbh->prepare($query)->execute($values);

        if ($imageSent) {
            $lastId = $this->dbh->lastInsertId();
            $filename = "$lastId.$extension";
            $destination = __DIR__ . '/../../photos/' . $filename;
            $tmp_name = $photo['tmp_name'];
            $query = "UPDATE users SET photo='$filename?v=0' WHERE id=$lastId";
            $this->dbh->query($query);
            move_uploaded_file($tmp_name, $destination);
        }
        $this->success = true;
        $this->message = 'Регистрация прошла успешно';
    }

    public function authorizeUser(string $login, string $password)
    {
        $query = 'SELECT * FROM users WHERE login = ? AND password = ?';
        $stmt = $this->dbh->prepare($query);
        $hash = $this->hash($password);
        $stmt->execute([$login, $hash]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($record === false) {
            $this->success = false;
            $this->message = 'Неверные имя пользователя и пароль';
        } else {
            $this->success = true;
            $this->message = 'Доступ открыт';
        }
    }

    public function loadAllUsers($order = null)
    {
        $query = 'SELECT id, login, name, age, description, photo FROM users';
        if ($order) {
            $query .= " ORDER BY age $order";
        }
        $result = $this->dbh->query($query);
        $this->table = $result->fetchAll(PDO::FETCH_ASSOC);

        foreach ($this->table as $key => $user) {
            if ($user['photo'] === null) {
                $this->table[$key]['photo'] = 'default-photo.png?v=0';
            }
            if ($user['age'] >= 18) {
                $this->table[$key]['adult'] = 'Coвершеннолетний';
            } else {
                $this->table[$key]['adult'] = 'Несoвершеннолетний';
            }
            $this->table[$key]['description'] =
                nl2br($this->table[$key]['description']);
        }
    }

    public function loadAllPhotos()
    {
        $query = 'SELECT id, name, photo FROM users WHERE photo IS NOT NULL';
        $result = $this->dbh->query($query);
        $this->photos = $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNameAndPhoto(int $userId)
    {
        $query = "SELECT id, name, photo FROM users WHERE id='$userId'";
        $result = $this->dbh->query($query)->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function changePhoto(int $userId, array $photo)
    {
        $extension = '';
        $this->checkImage($photo, $extension);
        $filename = "$userId.$extension";
        $destination = __DIR__ . '/../../photos/' . $filename;
        $tmp_name = $photo['tmp_name'];
        move_uploaded_file($tmp_name, $destination);

        $query = "SELECT photo FROM users WHERE id = $userId";
        $result = $this->dbh->query($query)->fetch(PDO::FETCH_ASSOC);
        $photo = $result['photo'];
        if ($photo === null) {
            $newPhoto = "$filename?v=0";
            $query = "UPDATE users SET photo='$newPhoto' WHERE id='$userId'";
            $this->dbh->query($query);
        } else {
            preg_match('/(.*)(\d+$)/', $photo, $matches);
            $name = $matches[1];
            $version = $matches[2];
            $version++;
            $newPhoto = $name . $version;
            $query = "UPDATE users SET photo='$newPhoto' WHERE id='$userId'";
            $this->dbh->query($query);
        }
    }
}
