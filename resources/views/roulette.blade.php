<?php
    if (!isset($_GET['id'])) {
        response(status: 406);
    }
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Roulette</title>
  <meta name="Main" content="Main">
  <link rel="stylesheet" href="{{URL::asset('css/style.css')}}">
</head>
<body>
    <div class="background" id="background">
        <img src="{{URL::asset('pic/NaeBarrelIcon.png')}}" class="icon">
        <div class="profile-balance"></div> <!--Сюда баланс-->
        <div class="profile"></div> <!--Сюда авы подгружайте-->
        <div class="barrel-name">Barrel-Name</div>
        <div class="openbarrel">
          <img class="simple-barrel">
          <div class="open" onclick="openBarrel()">PRICE</div>
        </div>
        <div class="drop-list"> <!--Тут гуглите Repeat-нотацию для grid-->

        </div>
    </div>
    <div class="openedItem hidden" id="openedItem"> <!--Сюда подгружаете дроп, который выпадет-->
      <div class="closeBarrel" id="closeBarrel" onclick="closeBarrel()">
        <p class="ok">OK</p>
      </div>
      <div class="actually-drop">
          <img class="actually-drop-size">
          <p class="actually-drop-name"></p>
      </div>
    </div>
    <script>
        let barrelId = <?php
            if (isset($_GET['id'])) {
                echo $_GET['id'];
            } else {
                echo 'false';
            }
        ?>
    </script>
    <script src="{{URL::asset('js/roulette.js')}}"></script>
</body>
</html>
