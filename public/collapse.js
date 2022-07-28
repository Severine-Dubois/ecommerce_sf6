console.log('collapse.js chargé');

const collapse = {
    init: function(){
        collapse.addEvents();

    },

addEvents: function(){
    // on sélectionne les boutons de navbar
    const navbarElements = document.querySelectorAll('[data-collapse-toggle]');
    console.log(navbarElements);

    // et on leur ajoute un écouteur d'évènement
    for (const navbarElement of navbarElements){
    navbarElement.addEventListener('click', collapse.handleCollapseToggle);
    }
    },


handleCollapseToggle: function(){
    // on choisi l'élément qui doit apparaitre

    const navCollapse =  document.getElementById('navbar-search');
    console.log(navCollapse);

    // et on va lui retirer/ajouter la classe hidden
    navCollapse.classList.toggle('hidden');
}

}

document.addEventListener('DOMContentLoaded', collapse.init);