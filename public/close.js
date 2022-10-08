console.log('close.js chargé');

const close = {
    init: function(){
        close.addEvents();

    },

addEvents: function(){
    // on sélectionne le bouton de la fenêtre
    const buttonElement = document.getElementById('close-warning');

    // et on lui ajoute un écouteur d'évènement
    buttonElement.addEventListener('click', close.handleCloseWindow);
    },


handleCloseWindow: function(){
    // on choisi l'élément qui doit apparaitre

    const windowElement =  document.getElementById('warning-window');

    // et on va lui retirer/ajouter la classe hidden
    windowElement.classList.add('hidden');
}

}

document.addEventListener('DOMContentLoaded', close.init);