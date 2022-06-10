if (!barrelId) {
    window.location.href = "/";
}

function openBarrel(){
    document.getElementById("openedItem").style.display = "block";
    document.getElementById("background").style.filter = "blur(5px)";

    let post = JSON.stringify({
        "case_id": barrelId
    });
    console.log(post);
    fetch("/api/cases/buy", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': localStorage.getItem("loginToken")
        },
        body: post
    }).then(response => {
        if (response.status === 401) {
            window.location.href = "/login/";
        }
        return response.json();
    }).then(data => {
        console.log(data)
        let pic = document.querySelector(".actually-drop-size");
        let name = document.querySelector(".actually-drop-name");

        if ('error_status' in data) {
            if (data['error_status'] === "NotEnoughMoney") {
                pic.src = '../pic/fish.png';
                name.textContent = "Не хватает денег!!!"
            }
        } else {
            document.querySelector(".actually-drop-size").src = '../pic/fish.png';
            name.textContent = data["name"];
        }
    });

}

function closeBarrel(){
    document.getElementById("openedItem").style.display = "none";
    document.getElementById("background").style.filter = "blur(0px)";
}

let rarity = {
    3: 'common',
    4: 'rare'
};

fetch("/api/cases/" + barrelId, {
    method: 'GET'
}).then(response => {
    return response.json();
}).then(data => {
    console.log(data);

    document.querySelector('.barrel-name').textContent = data['name'];
    document.querySelector('.simple-barrel').src = '../pic/simple_barrel.png';
    document.querySelector('.open').textContent = data['price'];

    let dropList = document.querySelector('.drop-list');
    data['items'].forEach(item => {
        let drop = document.createElement('div');
        drop.classList.add('drop');
        drop.classList.add(rarity[item['quality']]);

        let img = document.createElement('img');
        img.src = '../pic/fish.png';
        img.classList.add('fish-size');
        drop.append(img);

        let dropName = document.createElement('p');
        dropName.classList.add('drop-name');
        dropName.textContent = item['name'];
        drop.append(dropName);

        dropList.append(drop);
    })


});

