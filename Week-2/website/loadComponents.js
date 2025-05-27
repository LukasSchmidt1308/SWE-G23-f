// Funktion zum Laden der HTML-Komponenten ::: website/loadComponents.js
async function loadComponent(id, file) {

    const container = document.getElementById(id);                 // "Component".html anhand der ID finden -> z.B. "navigation"
    
    if(!container) {                                               // Error wenn Container nicht gefunden wurde
        console.error(`Container mit ID "${id}" nicht gefunden.`);
        return;
    }

    container.innerHTML = "<p>Lade...</p>";                         // Platzhalter während des Ladens

    try {
        const response = await fetch(file);                         // Datei wird geladen -> in "response" gespeichert

        if(!response.ok) {                                          // Error geworfen wenn die Datei nicht geladen werden konnte
            throw new Error(`Fehler beim Laden von ${file}: ${response.status}`);
        }

        const html = await response.text();                         // HTML-Inhalt der Datei wird in "html" gespeichert
        container.innerHTML = html;                                 // Inhalt wird auf der Seite eingefügt
    } catch(error) {
        console.error(`Fehler beim Laden von ${file}:`, error);
        container.innerHTML = `<p>Fehler beim Laden von ${file}</p>`;
    }

}

document.addEventListener('DOMContentLoaded', () => {

    loadComponent("banner", "banner.html");
    loadComponent("login", "login.html");

});

// Export für Tests
module.exports = {
    loadComponent
};
