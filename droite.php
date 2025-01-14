            <div class="w-full lg:w-1/3">
                <div class="bg-gray-700 p-4 rounded-t">
                    <div class="flex justify-between items-center">
                        <span class="font-bold uppercase">Infos</span>
                        <!--<span><i class="fas fa-user-friends"></i> 1000 connectés</span>-->
                    </div>
                </div>

                <div class="bg-gray-800 p-4 rounded-b">
                    <div class="font-bold uppercase mb-2">Gestion du forum</div>
                    <hr class="border-gray-600 mb-2">
                    <div class="mb-4"><a href="mailto:contact@geevey.com" alt="">contact@geevey.com</a><br><br>API disponible. Utilisez le paramètre "api" en GET, peu importe le contenu, sur une page du site.</div>
                    <!--<div class="center mb-4">
                        <i class="fas fa-envelope"></i> <a href="#" class="text-blue-400 hover:text-blue-600" title="Contacter les modérateurs">Contacter les modérateurs</a><br>
                        <i class="fas fa-list"></i> <a href="#" class="text-blue-400 hover:text-blue-600" title="Consulter les règles du forum">Consulter les règles du forum</a>
                    </div>-->
                    <hr class="border-gray-600 mb-2">
                    <div class="font-bold uppercase mb-2">Notes</div>
                    <hr class="border-gray-600 mb-2">
                    <ul>
                       <!-- <li><a href="#" class="text-blue-400 hover:text-blue-600" title="Hello world">Hello world</a></li>
                        <li><a href="#" class="text-blue-400 hover:text-blue-600" title="Hello world">Hello world</a></li>
                        <li><a href="#" class="text-blue-400 hover:text-blue-600" title="Hello world">Hello world</a></li>
                        <li><a href="#" class="text-blue-400 hover:text-blue-600" title="Hello world">Hello world</a></li>-->
						<span style="color:orange">Partenaire: <a href="https://jvflux.com/" target="_blank"><b>JVFlux</b></a><br></span>Ce site n'est pas associé à Jeuxvideo.com ou Webedia. Nous utilisons seulement des archives publiques.<br>Il est inutile de me spammer par e-mail pour supprimer un topic. Au contraire, en conséquence, je mettrais votre topic dans le bloc ci-dessous.
                    </ul>
                </div>

                <div class="bg-gray-800 p-4 rounded mt-4">
                    <div class="font-bold uppercase mb-2">Non-assumage</div>
                    <ul>
                        <!--<li><a href="#" class="text-blue-400 hover:text-blue-600" title="Hello world">Hello world</a></li>
                        <li><a href="#" class="text-blue-400 hover:text-blue-600" title="Hello world">Hello world</a></li>
                        <li><a href="#" class="text-blue-400 hover:text-blue-600" title="Hello world">Hello world</a></li>
                        <li><a href="#" class="text-blue-400 hover:text-blue-600" title="Hello world">Hello world</a></li>-->
						Personne n'a pas assumé de topic pour le moment.
                    </ul>
                </div>
            </div>
	<script>
document.addEventListener("DOMContentLoaded", function() {
    // Récupère toutes les balises <ns> de la page
    const nsElements = document.querySelectorAll("ns");

    // Parcourt chaque élément <ns> trouvé
    nsElements.forEach(ns => {
        // Crée un élément <img>
        const img = document.createElement("img");

        // Utilise le contenu de la balise <ns> pour l'attribut src de l'image
        img.src = ns.textContent.trim();

        // Définit la largeur de l'image
        img.width = 75;

        // Remplace la balise <ns> par la balise <img>
        ns.replaceWith(img);
    });
});

	</script>