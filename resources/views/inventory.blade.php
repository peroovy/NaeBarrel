<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Inventory</title>
  <meta name="Main" content="Main">
  <link rel="stylesheet" href="{{URL::asset("../css/style.css")}}">
</head>
<body>
    <div class="background">
        <img src="{{URL::asset("../pic/NaeBarrelIcon.png")}}" class="icon">
        <div class="profile-balance">
            <p class="balance"></p>
        </div> <!--Сюда баланс-->
        <div class="profile"></div> <!--Сюда авы подгружайте-->
        <div class="inv-list"> <!--Тут гуглите Repeat-нотацию для grid--></div>
    </div>

    <div class="openedItem hidden" id="0">
        <div class="closeBarrel" onclick="closeItem()">
            <p class="ok">OK</p>
        </div>
        <div class="actually-drop">
            <img class="actually-drop-size">
            <p class="actually-drop-name"></p>
        </div>
        <div class="sell-btn" onclick="sellItem()">
            <p style="position: absolute; font-size: 5vh; top: 10vh; left: 5vh; margin: 0">продать</p>
        </div>
        <div class="market-btn" onclick="putOnMarket()">
            <p style="position: absolute; font-size: 4vh; top: 15vh; left: 3vh; margin: 0">Выставить</p>
        </div>
        <input type="number" class="market-field">
    </div>

    <script>
        let clientLogin = "{{$login}}";
        console.log(clientLogin);
    </script>
    <script src="{{URL::asset("js/inventory.js")}}"></script>
</body>
</html>
