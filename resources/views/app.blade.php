<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App rencontre CESI</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
<nav class="navbar bg-dark p-0">
    <div class="container-fluid p-0">
        <a class="navbar-brand p-0" href="#">
            <img src="/images/logo.png" alt="Logo" style="max-height: 90px;">
        </a>
        <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-5 p-0 px-5" data-bs-toggle="dropdown" aria-label="Open user menu">
                <span id="user-avatar" class="avatar avatar-sm" style="background-image: url({{ auth()->user()->photo ?? './static/avatars/000m.jpg' }})"></span>
                <div class="d-none d-xl-block ps-2">
                    <div id="user-name">{{ auth()->user()->name ?? 'Utilisateur' }}</div>
                </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#profile-modal">Profil</button>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="dropdown-item">Déconnexion</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<div class="page">
    <header class="navbar navbar-expand-md navbar-light">
        <div class="container-xl">
            <h1 class="navbar-brand">App CESI rencontre</h1>
        </div>
    </header>

    <div class="page-wrapper">
        <div class="container-xl">
            <ul class="nav nav-tabs" id="menuTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#liste-tab-pane" type="button" role="tab" aria-controls="liste-tab-pane" aria-selected="true">Liste</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#carte-tab-pane" type="button" role="tab" aria-controls="carte-tab-pane" aria-selected="false">Carte</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="liste-tab-pane" role="tabpanel" aria-labelledby="liste-tab" tabindex="0">
                    <div class="row row-cards" id="users-list">
                        <!-- Liste des utilisateurs sera remplie via JS -->
                    </div>

                    <!-- Liste des utilisateurs pour les admins -->
                    @role('admin')
                    <section>
                        <h3>Liste des utilisateurs</h3>
                        <ul id="user-list" class="list-group">
                            @foreach ($users as $user)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $user->name }} - {{ $user->email }}
                                    <button class="btn btn-danger btn-sm" onclick="deleteUser({{ $user->id }})">Supprimer</button>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                    @endrole

                    <div class="d-flex mt-4">
                        <ul class="pagination ms-auto">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                        <path d="M15 6l-6 6l6 6"></path>
                                    </svg>
                                    prev
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">4</a></li>
                            <li class="page-item"><a class="page-link" href="#">5</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">
                                    next
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                        <path d="M9 6l6 6l-6 6"></path>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="tab-pane fade" id="carte-tab-pane" role="tabpanel" aria-labelledby="carte-tab" tabindex="0">
                    <div class="card">
                        <div class="card-body">
                            <div id="users-map" style="height: 500px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" id="profile-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mon profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container w-50 px-lg-5 mt-5">
                    <h2>Mon Profil</h2>
                    <div class="mb-5 border-bottom-1">
                        <label for="photo" class="form-label">Photo de profil :</label>
                        <input type="file" accept="image/*" name="photo" id="photo" class="form-control">
                    </div>
                    <div id="profile-info" class="mb-4">
                        <!-- Les données seront chargées ici -->
                    </div>
                    <form id="profile-form" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nom :</label>
                            <input type="text" name="name" id="name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Présentation :</label>
                            <textarea name="description" id="description" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="birth" class="form-label">Date de naissance :</label>
                            <input type="date" name="birth" id="birth" class="form-control">
                        </div>
                    </form>
                    <div id="message" class="mt-3"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" id="profile-save">Sauvegarder</button>
            </div>
        </div>
    </div>
</div>

<div class="modal" tabindex="-1" id="chat">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container w-50 px-lg-5 mt-5">
                    <!-- Contenu du chat -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script>
    // Charger les données utilisateur pour la navbar et le profil
    async function loadUserData() {
        try {
            console.log('Requête vers /web/profile');
            const response = await fetch('/web/profile', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            });

            console.log('Statut /web/profile:', response.status, response.statusText);

            if (!response.ok) {
                throw new Error(`Erreur ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Données utilisateur:', data);
            document.getElementById('name').value = data.name || '';
            document.getElementById('description').value = data.description || '';
            document.getElementById('birth').value = data.birth || '';
            document.getElementById('profile-info').innerHTML = `
                <p><strong>Nom :</strong> ${data.name || 'Non défini'}</p>
                <p><strong>Email :</strong> ${data.email || 'Non défini'}</p>
                <p><strong>Présentation :</strong> ${data.description || 'Non définée'}</p>
                <p><strong>Date de naissance :</strong> ${data.birth || 'Non définie'}</p>
                ${data.photo ? `<img src="${data.photo}" alt="Photo de profil" class="img-thumbnail" style="max-width: 200px;">` : ''}
            `;
            document.getElementById('user-name').textContent = data.name || 'Utilisateur';
            document.getElementById('user-avatar').style.backgroundImage = `url(${data.photo || './static/avatars/000m.jpg'})`;
        } catch (error) {
            console.error('Erreur chargement utilisateur:', error);
            document.getElementById('message').innerText = 'Erreur lors du chargement du profil';
        }
    }

    // Charger les utilisateurs pour la liste
    async function loadUsers() {
        try {
            console.log('Requête vers /web/users');
            const response = await fetch('/web/users', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            });

            console.log('Statut /web/users:', response.status, response.statusText);

            if (!response.ok) {
                throw new Error(`Erreur ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('Données utilisateurs:', data);

            const userList = document.getElementById('users-list');
            userList.innerHTML = '';

            data.forEach(user => {
                const userCol = document.createElement('div');
                userCol.classList.add('col-md-6', 'col-lg-3');

                const card = document.createElement('div');
                card.classList.add('card');
                userCol.appendChild(card);

                const cardBody = document.createElement('div');
                cardBody.classList.add('card-body', 'p-4', 'text-center');

                const avatar = document.createElement('span');
                avatar.classList.add('avatar', 'avatar-xl', 'mb-3', 'rounded');
                avatar.style.backgroundImage = `url(${user.photo || './static/avatars/000m.jpg'})`;

                const userName = document.createElement('h3');
                userName.classList.add('m-0', 'mb-1');
                const userLink = document.createElement('a');
                userLink.href = '#';
                userLink.textContent = user.name || 'Nom inconnu';
                userName.appendChild(userLink);

                const userPost = document.createElement('div');
                userPost.classList.add('text-secondary');
                userPost.textContent = user.description || 'Position inconnue';

                const badgeContainer = document.createElement('div');
                badgeContainer.classList.add('mt-3');
                const badge = document.createElement('span');
                badge.classList.add('badge', 'bg-purple-lt');
                badge.textContent = user.role || 'Rôle inconnu';
                badgeContainer.appendChild(badge);

                cardBody.appendChild(avatar);
                cardBody.appendChild(userName);
                cardBody.appendChild(userPost);
                cardBody.appendChild(badgeContainer);
                card.appendChild(cardBody);

                const actionContainer = document.createElement('div');
                actionContainer.classList.add('d-flex');

                const messageLink = document.createElement('a');
                messageLink.href = '#';
                messageLink.classList.add('card-btn');
                messageLink.setAttribute("data-bs-toggle", 'modal');
                messageLink.setAttribute("data-bs-target", "#chat");
                messageLink.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2 text-muted icon-3">
                        <path d="M3 7a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-10z"></path>
                        <path d="M3 7l9 6l9 -6"></path>
                    </svg>
                    Message
                `;
                actionContainer.appendChild(messageLink);

                const callLink = document.createElement('a');
                callLink.href = '#';
                callLink.classList.add('card-btn');
                callLink.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2 text-muted icon-3">
                        <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2"></path>
                    </svg>
                    Contact
                `;
                actionContainer.appendChild(callLink);

                card.appendChild(actionContainer);
                userList.appendChild(userCol);
            });
        } catch (error) {
            console.error('Erreur lors de la récupération des utilisateurs:', error);
            document.getElementById('users-list').innerHTML = '<p class="text-danger">Erreur lors du chargement des utilisateurs.</p>';
        }
    }

    // Supprimer un utilisateur
    async function deleteUser(userId) {
        if (!confirm("Es-tu sûr de vouloir supprimer cet utilisateur ?")) return;

        try {
            const response = await fetch(`/admin/users/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            if (!response.ok) {
                throw new Error(`Erreur ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();
            alert(result.message || 'Utilisateur supprimé');
            location.reload(); // Recharge la page pour mettre à jour la liste
        } catch (error) {
            console.error('Erreur suppression utilisateur:', error);
            alert('Erreur lors de la suppression de l’utilisateur');
        }
    }

    // Initialiser la carte Leaflet
    function initMap() {
        console.log('Initialisation de la carte Leaflet');
        var map = L.map('users-map').setView([48.8566, 2.3522], 13); // Paris par défaut
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var lat = position.coords.latitude;
                var lon = position.coords.longitude;
                console.log('Position géolocalisée:', lat, lon);
                map.setView([lat, lon], 15);
                L.marker([lat, lon]).addTo(map)
                    .bindPopup("Vous êtes ici !")
                    .openPopup();
            }, function(error) {
                console.error("Erreur de géolocalisation : ", error);
            });
        } else {
            console.error("La géolocalisation n’est pas supportée par ce navigateur.");
        }
    }

    // Sauvegarde du profil
    document.getElementById('profile-save').addEventListener('click', async () => {
        console.log("Sauvegarde du profil");
        const formData = new FormData();
        formData.append('name', document.getElementById('name').value || '');
        formData.append('description', document.getElementById('description').value || '');
        formData.append('birth', document.getElementById('birth').value || '');
        formData.append('_method', 'PATCH');
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch('/web/profile', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();
            console.log('Réponse sauvegarde:', data);
            if (response.ok) {
                document.getElementById('message').innerText = 'Profil mis à jour avec succès';
                document.getElementById('message').className = 'mt-3 text-success';
                document.getElementById('user-name').textContent = data.user.name || 'Utilisateur';
            } else {
                document.getElementById('message').innerText = data.message || 'Erreur lors de la sauvegarde';
                document.getElementById('message').className = 'mt-3 text-danger';
            }
        } catch (error) {
            console.error('Erreur sauvegarde:', error);
            document.getElementById('message').innerText = 'Erreur inattendue lors de la sauvegarde';
            document.getElementById('message').className = 'mt-3 text-danger';
        }
    });

    // Sauvegarde de la photo
    document.getElementById('photo').addEventListener('change', async (e) => {
        console.log("Sauvegarde de la photo");
        const formData = new FormData();
        formData.append('photo', e.target.files[0]);
        formData.append('_method', 'PATCH');
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch('/web/profile', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();
            console.log('Réponse sauvegarde photo:', data);
            if (response.ok) {
                document.getElementById('message').innerText = 'Photo mise à jour avec succès';
                document.getElementById('message').className = 'mt-3 text-success';
                document.getElementById('user-avatar').style.backgroundImage = `url(${data.user.photo || './static/avatars/000m.jpg'})`;
            } else {
                document.getElementById('message').innerText = data.message || 'Erreur lors de la sauvegarde';
                document.getElementById('message').className = 'mt-3 text-danger';
            }
        } catch (error) {
            console.error('Erreur sauvegarde photo:', error);
            document.getElementById('message').innerText = 'Erreur inattendue lors de la sauvegarde';
            document.getElementById('message').className = 'mt-3 text-danger';
        }
    });

    // Chargement initial
    console.log('Chargement des données utilisateur, utilisateurs et carte');
    loadUserData();
    loadUsers();
    initMap();
</script>
</body>
</html>
