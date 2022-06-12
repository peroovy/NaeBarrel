<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NaeBarrel</title>
    <meta name="Main" content="Main">
    <link rel="stylesheet" href="{{URL::asset('css/style.css')}}">
</head>
<body>
<div class="background">
    <img src="{{URL::asset('pic/NaeBarrelIcon.png')}}" class="icon">
    <div class="profile-balance">
        <p class="balance"></p>
    </div> <!--Сюда баланс-->
    <div class="profile"></div> <!--Сюда авы подгружайте-->
    <div class="inv-list"> <!--Тут гуглите Repeat-нотацию для grid-->

    </div>
    <div class="openedItem hidden" id="openedItem"> <!--Сюда подгружаете дроп, который выпадет-->
        <div class="closeBarrel" id="closeBarrel" onclick="closeBarrel()">
            <p class="ok">OK</p>
        </div>
        <div class="actually-drop">
            <img class="actually-drop-size" src="{{URL::asset('pic/fish.png')}}">
            <p class="actually-drop-name">Не хватает денег!!!</p>
        </div>
    </div>
</div>
<script src="{{URL::asset('js/market.js')}}"></script>
</body>
</html>
