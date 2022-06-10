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
        <div class="profile-balance"></div> <!--Сюда баланс-->
        <div class="profile"></div> <!--Сюда авы подгружайте-->
        <div class="inv-list"> <!--Тут гуглите Repeat-нотацию для grid-->

        </div>
    </div>
    <script>
        let clientLogin = "{{$login}}";
        console.log(clientLogin);
    </script>
    <script src="{{URL::asset("../js/inventory.js")}}"></script>
</body>
</html>
