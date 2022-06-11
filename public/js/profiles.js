let profilesList = document.querySelector('.profiles-list');

fetch("/api/clients", {
    method: 'GET'
}).then(response => {
    return response.json();
}).then(data => {
    data.forEach(profile => {
        let button = document.createElement('a');
        button.href = '/profile/' + profile['login'];

        let card = document.createElement('div');
        card.classList.add('profile-card');

        let name = document.createElement('p');
        name.classList.add('profile-name');
        name.textContent = profile['login'];
        card.append(name);

        button.append(card);
        profilesList.append(button);
    })
});
