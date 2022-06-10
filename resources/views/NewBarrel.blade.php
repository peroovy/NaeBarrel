<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>NewBarrel</title>
  <meta name="Main" content="Main">
  <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="background">
        <img src="NaeBarrelIcon.png" class="icon">
        <div class="profile-balance"></div> <!--Сюда баланс-->
        <div class="profile"></div> <!--Сюда авы подгружайте-->
        <div class="fill-name"><input type="text" class="type-name" placeholder="Type Barrel Name"></div>
        <div class="fill-price"><input type="text" class="type-name" placeholder="Type Barrel Price"></div>
        <div class="place-list"> <!--Тут гуглите Repeat-нотацию для grid-->
          <div class="place"><img src="plus.png" class="place-size">
          </div>
        </div>
        <form class="choose-image">
          <label class="radio-inline">
            <input type="radio" name="optradio">
            <img src="simple_barrel.png" class="choose-size">
          </label>
          <label class="radio-inline">
            <input type="radio" name="optradio">
            <img src="simple_barrel.png" class="choose-size">
          </label>
          <label class="radio-inline">
            <input type="radio" name="optradio">
            <img src="simple_barrel.png" class="choose-size">
          </label>
        </form>
    </div>
    <script src="js/scripts.js"></script>
</body>
</html>