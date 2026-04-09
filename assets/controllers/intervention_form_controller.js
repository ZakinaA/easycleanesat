import { Controller } from '@hotwired/stimulus';

/**
 * Contrôleur Stimulus pour gérer le formulaire d'intervention dynamique.
 * 
 * Ce contrôleur gère les interactions entre les listes déroulantes du formulaire :
 * - Quand un client est sélectionné → charge ses sites
 * - Quand un site est sélectionné → charge ses contrats et zones
 * 
 * Stimulus est un framework JavaScript qui se connecte automatiquement aux éléments HTML
 * via des attributs data-controller, data-target, data-action, etc.
 */
export default class extends Controller {
    /**
     * Définition des "targets" : éléments HTML que ce contrôleur peut manipuler.
     * Stimulus les rend accessibles via this.clientTarget, this.siteTarget, etc.
     * Dans le HTML, on les marque avec data-intervention-form-target="client"
     */
    static targets = ['client', 'site', 'contrat', 'zone'];
    
    /**
     * Définition des "values" : données passées du HTML vers le JavaScript.
     * Ces URLs sont générées par Symfony (path()) dans le template HTML
     * et deviennent accessibles via this.sitesUrlValue, this.contratsUrlValue, etc.
     * Dans le HTML : data-intervention-form-sites-url-value="..."
     */
    static values = {
        sitesUrl: String,      // URL de l'API pour récupérer les sites d'un client
        contratsUrl: String,   // URL de l'API pour récupérer les contrats d'un site
        zonesUrl: String       // URL de l'API pour récupérer les zones d'un site
    };

    /**
     * Méthode appelée automatiquement quand le contrôleur est connecté au DOM.
     * C'est l'équivalent de document.addEventListener('DOMContentLoaded', ...)
     */
    connect() {
        console.log('Intervention form controller connected');
    }

    /**
     * Gestionnaire d'événement déclenché quand l'utilisateur change le client.
     * Déclaré dans le HTML avec : data-action="change->intervention-form#onClientChange"
     * 
     * @param {Event} event L'événement change du select
     */
    async onClientChange(event) {
        const clientId = event.target.value;  // Récupère l'ID du client sélectionné
        
        // Réinitialiser tous les champs qui dépendent du client
        // (site, contrat, zone) car ils ne sont plus valides
        this.resetSelect(this.siteTarget);
        this.resetSelect(this.contratTarget);
        this.resetSelect(this.zoneTarget);

        // Si un client est sélectionné (pas l'option vide), charger ses sites
        if (clientId) {
            await this.loadSites(clientId);
        }
    }

    /**
     * Gestionnaire d'événement déclenché quand l'utilisateur change le site.
     * Déclaré dans le HTML avec : data-action="change->intervention-form#onSiteChange"
     * 
     * @param {Event} event L'événement change du select
     */
    async onSiteChange(event) {
        const siteId = event.target.value;  // Récupère l'ID du site sélectionné
        
        // Réinitialiser les champs qui dépendent du site
        this.resetSelect(this.contratTarget);
        this.resetSelect(this.zoneTarget);

        // Si un site est sélectionné, charger ses contrats ET ses zones en parallèle
        if (siteId) {
            await this.loadContrats(siteId);
            await this.loadZones(siteId);
        }
    }

    /**
     * Charge les sites d'un client via une requête AJAX.
     * Appelle l'API Symfony qui retourne du JSON : [{id: 1, nom: "Site A"}, ...]
     * 
     * @param {number} clientId L'ID du client
     */
    async loadSites(clientId) {
        try {
            // Remplace le placeholder __CLIENT_ID__ par l'ID réel dans l'URL
            const url = this.sitesUrlValue.replace('__CLIENT_ID__', clientId);
            
            // Fetch : effectue la requête HTTP GET vers l'API
            const response = await fetch(url);
            
            // Parse la réponse JSON en objet JavaScript
            const sites = await response.json();
            
            // Remplit le select "site" avec les données reçues
            this.populateSelect(this.siteTarget, sites, 'id', 'nom');
        } catch (error) {
            console.error('Erreur lors du chargement des sites:', error);
        }
    }

    /**
     * Charge les contrats d'un site via une requête AJAX.
     * 
     * @param {number} siteId L'ID du site
     */
    async loadContrats(siteId) {
        try {
            // Remplace __SITE_ID__ par l'ID réel
            const url = this.contratsUrlValue.replace('__SITE_ID__', siteId);
            const response = await fetch(url);
            const contrats = await response.json();
            
            // Remplit le select "contrat" avec les données reçues
            // Utilise 'numero' au lieu de 'nom' car un contrat a un numéro
            this.populateSelect(this.contratTarget, contrats, 'id', 'numero');
        } catch (error) {
            console.error('Erreur lors du chargement des contrats:', error);
        }
    }

    /**
     * Charge les zones d'un site via une requête AJAX.
     * 
     * @param {number} siteId L'ID du site
     */
    async loadZones(siteId) {
        try {
            // Remplace __SITE_ID__ par l'ID réel
            const url = this.zonesUrlValue.replace('__SITE_ID__', siteId);
            const response = await fetch(url);
            const zones = await response.json();
            
            // Remplit le select "zone" avec les données reçues
            this.populateSelect(this.zoneTarget, zones, 'id', 'nom');
        } catch (error) {
            console.error('Erreur lors du chargement des zones:', error);
        }
    }

    /**
     * Réinitialise un élément select : le vide et le désactive.
     * Utilisé quand une sélection précédente change et invalide ce champ.
     * 
     * @param {HTMLSelectElement} selectElement Le select à réinitialiser
     */
    resetSelect(selectElement) {
        // Supprime toutes les options et remet juste l'option par défaut
        selectElement.innerHTML = '<option value="">Sélectionnez une option</option>';
        
        // Désactive le champ (grisé) jusqu'à ce qu'il soit rempli
        selectElement.disabled = true;
    }

    /**
     * Remplit un élément select avec une liste d'éléments.
     * Crée les balises <option> dynamiquement à partir des données reçues.
     * 
     * @param {HTMLSelectElement} selectElement Le select à remplir
     * @param {Array} items Tableau d'objets [{id: 1, nom: "..."}, ...]
     * @param {string} valueKey Nom de la propriété pour l'attribut value de l'option
     * @param {string} labelKey Nom de la propriété pour le texte visible de l'option
     */
    populateSelect(selectElement, items, valueKey, labelKey) {
        // Réinitialise le select
        selectElement.innerHTML = '<option value="">Sélectionnez une option</option>';
        
        // Pour chaque élément reçu, créer une balise <option>
        items.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];        // Ex: <option value="1">
            option.textContent = item[labelKey];  // Ex: Site Alpha</option>
            selectElement.appendChild(option);
        });
        
        // Réactive le champ maintenant qu'il contient des options
        selectElement.disabled = false;
    }
}
