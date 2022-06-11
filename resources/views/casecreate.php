<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NaeBarrel</title>
    <meta name="Main" content="Main">
</head>
<body>

<form enctype="multipart/form-data" method="post" action="/api/cases">
    <label>
        Название кейса:<br>
        <input name="name" type="text"><br>
    </label>
    <br>
    <label>
        Описание кейса:<br>
        <input name="description" type="text"><br>
    </label>
    <br>
    <label>
        Цена:<br>
        <input name="price" type="number" min="1" max="99999"><br>
    </label>
    <br>
    <label>
        Изображение:<br>
        <input name="picture" type="file" accept=".jpg, .jpeg, .png"><br>
    </label>
</form>

</body>
</html>
