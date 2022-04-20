class DW_Controller
{
    constructor()
    {
        // À ce stade-ci, le DOM n'est pas encore prêt, car nous sommes dans le <head>.
        // Permet d'instancier des classes utilitaires par exemple.
    }

    run()
    {
        // Désormais, le DOM est prêt, nous pouvons commencer à le manipuler.
        // Permer d'instancier des classes de composants d'interface par exemple.
    }
}

window.dw = new DW_Controller();
window.addEventListener('load', () => window.dw.run());
