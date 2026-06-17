import { Controller } from '@hotwired/stimulus';

/**
 * Contrôleur Stimulus pour la section supports/actions du formulaire intervention.
 *
 * Responsabilités :
 * - Chargement AJAX des supports selon la zone sélectionnée
 * - Toggle sélection des supports (style tile-card)
 * - Drag-and-drop natif HTML5 pour réordonner les supports sélectionnés
 * - Barre de recherche pour ajouter des actions (tri starts-with en premier)
 * - Filtres par code de nécessaire (t.X, SUP.X, MAT.X, REU.X, ACC.X, CON.X)
 * - Checkboxes pour associer chaque action aux supports sélectionnés
 * - Sérialisation JSON dans un champ hidden pour soumission au serveur
 */
export default class extends Controller {
    static targets = [
        'zone',
        'supportsSection',
        'supportsGrid',
        'actionsSection',
        'actionSearch',
        'actionDropdown',
        'filterChips',
        'actionsList',
        'hiddenData',
    ];

    static values = {
        supportsUrl: String,
        actions: Array,
        initialData: String,
        initialZoneId: String,
        pictoUrl: String,
        contenantUrl: String,
        moyenDosageUrl: String,
        tempsContactUrl: String,
    };

    // État interne
    allSupports = [];        // [{id, nom, type_support_id}] depuis l'API
    selectedSupports = [];   // [{supportClientId, orderPosition, nom}]
    addedActions = [];       // [{actionsId, label, tache, necessaires, meo, suppInterPositions}]
    activeFilters = [];      // codes de nécessaires actifs (ex: ['t.3', 'SUP.1'])
    dragSrcId = null;
    isDragging = false;

    connect() {
        // Pré-charger les données en mode édition
        const initial = this.initialDataValue;
        if (initial && initial !== 'null' && initial !== '') {
            try {
                const parsed = JSON.parse(initial);
                this.selectedSupports = (parsed.supports || []).map(s => ({
                    supportClientId: s.support_client_id,
                    orderPosition: s.order_position,
                    nom: s.nom || '',
                }));
                this.addedActions = (parsed.actions || []).map(a => {
                    const full = this.actionsValue.find(av => av.id === a.actionsId) || {};
                    return {
                        actionsId: a.actionsId,
                        label: full.label || a.label || ('Action #' + a.actionsId),
                        tache: full.tache || a.tache || null,
                        necessaires: full.necessaires || a.necessaires || [],
                        meo: full.meo || a.meo || null,
                        suppInterPositions: [...(a.suppInterPositions || [])],
                    };
                });
            } catch (e) {
                console.error('supp_inter: erreur parsing initialData', e);
            }
        }

        // Charger les supports si une zone est déjà sélectionnée
        const zoneId = this.hasZoneTarget ? this.zoneTarget.value : this.initialZoneIdValue;
        if (zoneId) {
            this.loadSupports(zoneId);
        }

        // Rendre les chips de filtre (dépendent uniquement de actionsValue)
        this.renderFilterSelects();
    }

    onZoneChange(event) {
        const zoneId = event.target.value;
        // Réinitialiser l'état
        this.selectedSupports = [];
        this.addedActions = [];
        this.allSupports = [];
        this.activeFilters = [];
        this.serialize();
        this.renderFilterSelects();

        if (zoneId) {
            this.loadSupports(zoneId);
        } else {
            this.hideSections();
        }
    }

    async loadSupports(zoneId) {
        try {
            const url = this.supportsUrlValue.replace('__ZONE_ID__', zoneId);
            const response = await fetch(url);
            this.allSupports = await response.json();

            // En mode édition, les noms peuvent manquer dans selectedSupports → les remplir
            this.selectedSupports.forEach(sel => {
                if (!sel.nom) {
                    const found = this.allSupports.find(s => s.id === sel.supportClientId);
                    if (found) sel.nom = found.nom;
                }
            });

            this.renderSupportsGrid();
            this.showSupportsSection();

            if (this.selectedSupports.length > 0) {
                this.renderActionsList();
                this.showActionsSection();
            }
        } catch (e) {
            console.error('supp_inter: erreur chargement supports', e);
        }
    }

    // ─── Rendu de la grille de supports ────────────────────────────────────────

    renderSupportsGrid() {
        const grid = this.supportsGridTarget;
        grid.innerHTML = '';

        if (this.allSupports.length === 0) {
            const msg = document.createElement('p');
            msg.className = 'text-muted fst-italic';
            msg.textContent = 'Aucun support n\'est associé à cette zone.';
            grid.appendChild(msg);
            this.serialize();
            return;
        }

        // Supports sélectionnés en premier (triés par position)
        const sortedSelected = [...this.selectedSupports].sort((a, b) => a.orderPosition - b.orderPosition);
        for (const sel of sortedSelected) {
            const support = this.allSupports.find(s => s.id === sel.supportClientId);
            if (support) {
                grid.appendChild(this.createSupportCard(support, true, sel.orderPosition));
            }
        }

        // Supports non sélectionnés
        for (const support of this.allSupports) {
            if (!this.selectedSupports.some(s => s.supportClientId === support.id)) {
                grid.appendChild(this.createSupportCard(support, false, null));
            }
        }

        this.serialize();
    }

    createSupportCard(support, selected, position) {
        const card = document.createElement('div');
        card.className = 'tile-item supp-card' + (selected ? ' selected' : '');
        card.dataset.supportId = support.id;

        const nameSpan = document.createElement('span');
        nameSpan.textContent = support.nom;
        card.appendChild(nameSpan);

        if (selected && position !== null) {
            const badge = document.createElement('span');
            badge.className = 'supp-position-badge';
            badge.textContent = 'Pos. ' + position;
            card.appendChild(badge);

            card.setAttribute('draggable', 'true');
            card.addEventListener('dragstart', this._onDragStart.bind(this));
            card.addEventListener('dragover', this._onDragOver.bind(this));
            card.addEventListener('dragleave', this._onDragLeave.bind(this));
            card.addEventListener('drop', this._onDrop.bind(this));
            card.addEventListener('dragend', this._onDragEnd.bind(this));
        }

        card.addEventListener('click', () => {
            if (this.isDragging) return;
            this.toggleSupport(support.id, support.nom);
        });

        return card;
    }

    toggleSupport(supportId, nom) {
        const idx = this.selectedSupports.findIndex(s => s.supportClientId === supportId);

        if (idx >= 0) {
            // Désélectionner
            const removedPos = this.selectedSupports[idx].orderPosition;
            this.selectedSupports.splice(idx, 1);

            // Renommer les positions restantes
            this.selectedSupports.sort((a, b) => a.orderPosition - b.orderPosition);
            this.selectedSupports.forEach((s, i) => { s.orderPosition = i + 1; });

            // Mettre à jour les positions dans les actions
            this.addedActions.forEach(a => {
                a.suppInterPositions = a.suppInterPositions
                    .filter(p => p !== removedPos)
                    .map(p => p > removedPos ? p - 1 : p);
            });
            // Supprimer les actions qui n'ont plus aucun support
            this.addedActions = this.addedActions.filter(a => a.suppInterPositions.length > 0);
        } else {
            // Sélectionner à la fin
            const newPos = this.selectedSupports.length + 1;
            this.selectedSupports.push({ supportClientId: supportId, orderPosition: newPos, nom });
        }

        this.renderSupportsGrid();
        this.renderActionsList();

        if (this.selectedSupports.length > 0) {
            this.showActionsSection();
        } else {
            this.hideActionsSection();
        }
    }

    // ─── Drag-and-drop ─────────────────────────────────────────────────────────

    _onDragStart(event) {
        this.isDragging = true;
        this.dragSrcId = parseInt(event.currentTarget.dataset.supportId);
        event.dataTransfer.effectAllowed = 'move';
    }

    _onDragOver(event) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        event.currentTarget.classList.add('drag-over');
    }

    _onDragLeave(event) {
        event.currentTarget.classList.remove('drag-over');
    }

    _onDrop(event) {
        event.stopPropagation();
        const targetEl = event.currentTarget;
        targetEl.classList.remove('drag-over');

        const tgtId = parseInt(targetEl.dataset.supportId);
        if (this.dragSrcId === tgtId) return;

        const srcIdx = this.selectedSupports.findIndex(s => s.supportClientId === this.dragSrcId);
        const tgtIdx = this.selectedSupports.findIndex(s => s.supportClientId === tgtId);
        if (srcIdx < 0 || tgtIdx < 0) return;

        // Positions avant échange
        const srcOldPos = this.selectedSupports[srcIdx].orderPosition;
        const tgtOldPos = this.selectedSupports[tgtIdx].orderPosition;

        // Échanger les supports dans le tableau
        [this.selectedSupports[srcIdx], this.selectedSupports[tgtIdx]] =
            [this.selectedSupports[tgtIdx], this.selectedSupports[srcIdx]];

        this.selectedSupports.forEach((s, i) => { s.orderPosition = i + 1; });

        const srcNewPos = this.selectedSupports[tgtIdx].orderPosition;
        const tgtNewPos = this.selectedSupports[srcIdx].orderPosition;

        // Mettre à jour les positions dans les actions
        this.addedActions.forEach(a => {
            a.suppInterPositions = a.suppInterPositions.map(p => {
                if (p === srcOldPos) return srcNewPos;
                if (p === tgtOldPos) return tgtNewPos;
                return p;
            });
        });

        this.renderSupportsGrid();
        this.renderActionsList();
    }

    _onDragEnd(event) {
        event.currentTarget.classList.remove('drag-over');
        // Délai pour éviter que le clic post-drag ne déclenche toggleSupport
        setTimeout(() => { this.isDragging = false; }, 50);
        this.dragSrcId = null;
    }

    // ─── Filtres par code de nécessaire ────────────────────────────────────────

    /**
     * Extrait tous les codes de nécessaires uniques depuis toutes les actions,
     * groupés par prefix de code (t, SUP, MAT, REU, ACC, CON…).
     * @returns {{ [prefix: string]: { label: string, items: Array<{code, nom}> } }}
     */
    _buildNecessaireGroups() {
        const groups = {};

        this.actionsValue.forEach(action => {
            // Tâche (typeId 4, stockée séparément)
            if (action.tache) {
                const code = action.tache.code;
                const prefix = code.split('.')[0].toUpperCase();
                if (!groups[prefix]) groups[prefix] = { label: 'Tâche', items: [] };
                if (!groups[prefix].items.some(i => i.code === code)) {
                    groups[prefix].items.push({ code, nom: action.tache.nom });
                }
            }
            // Autres nécessaires
            (action.necessaires || []).forEach(nec => {
                const code = nec.code;
                const prefix = code.split('.')[0].toUpperCase();
                const label = nec.type_nom
                    ? nec.type_nom.charAt(0).toUpperCase() + nec.type_nom.slice(1)
                    : prefix;
                if (!groups[prefix]) groups[prefix] = { label, items: [] };
                if (!groups[prefix].items.some(i => i.code === code)) {
                    groups[prefix].items.push({ code, nom: nec.nom || code });
                }
            });
        });

        // Trier les items dans chaque groupe par numéro
        Object.values(groups).forEach(group => {
            group.items.sort((a, b) => {
                const numA = parseInt(a.code.split('.')[1]) || 0;
                const numB = parseInt(b.code.split('.')[1]) || 0;
                return numA - numB;
            });
        });

        // Trier les groupes : T en premier, puis alphabétique
        const orderedPrefixes = ['T', 'SUP', 'MAT', 'REU', 'ACC', 'CON'];
        const sorted = {};
        orderedPrefixes.forEach(p => { if (groups[p]) sorted[p] = groups[p]; });
        Object.entries(groups).forEach(([p, g]) => { if (!sorted[p]) sorted[p] = g; });
        return sorted;
    }

    renderFilterSelects() {
        if (!this.hasFilterChipsTarget) return;
        const container = this.filterChipsTarget;
        container.innerHTML = '';

        const groups = this._buildNecessaireGroups();
        if (Object.keys(groups).length === 0) return;

        const row = document.createElement('div');
        row.className = 'filter-selects-row';

        Object.entries(groups).forEach(([prefix, group]) => {
            if (group.items.length === 0) return;

            const groupDiv = document.createElement('div');
            groupDiv.className = 'filter-select-group';

            const label = document.createElement('div');
            label.className = 'filter-select-label';
            label.textContent = group.label;
            groupDiv.appendChild(label);

            const list = document.createElement('div');
            list.className = 'filter-list';

            group.items.forEach(item => {
                const itemEl = document.createElement('div');
                itemEl.className = 'filter-list-item' + (this.activeFilters.includes(item.code) ? ' active' : '');
                itemEl.textContent = item.nom;
                itemEl.dataset.code = item.code;

                itemEl.addEventListener('click', () => {
                    const prefix = item.code.split('.')[0].toUpperCase();
                    const wasActive = this.activeFilters.includes(item.code);

                    // Un seul choix par groupe : on vide d'abord tous les codes du même préfixe
                    this.activeFilters = this.activeFilters.filter(c => c.split('.')[0].toUpperCase() !== prefix);

                    // Si l'item n'était pas actif, on le sélectionne
                    if (!wasActive) {
                        this.activeFilters.push(item.code);
                    }

                    // Re-rendre pour mettre à jour l'état visuel du groupe
                    this.renderFilterSelects();

                    // Mettre à jour le dropdown s'il est visible
                    if (this.hasActionDropdownTarget && this.actionDropdownTarget.style.display !== 'none') {
                        const query = this.hasActionSearchTarget ? this.actionSearchTarget.value.toLowerCase().trim() : '';
                        this._applySearch(query);
                    }
                });

                list.appendChild(itemEl);
            });

            groupDiv.appendChild(list);
            row.appendChild(groupDiv);
        });

        container.appendChild(row);
    }

    // ─── Pool filtré par chips actifs ──────────────────────────────────────────

    /**
     * Retourne les actions non encore ajoutées, filtrées par activeFilters.
     * Logique : OR au sein d'un même prefix, AND entre prefixes différents.
     */
    _getFilteredPool() {
        let pool = this.actionsValue.filter(a =>
            !this.addedActions.some(added => added.actionsId === a.id)
        );

        if (this.activeFilters.length > 0) {
            // Grouper les filtres actifs par prefix
            const byPrefix = {};
            this.activeFilters.forEach(code => {
                const prefix = code.split('.')[0].toUpperCase();
                if (!byPrefix[prefix]) byPrefix[prefix] = [];
                byPrefix[prefix].push(code);
            });

            pool = pool.filter(a => {
                // Codes disponibles dans l'action (tâche + nécessaires)
                const codes = (a.necessaires || []).map(n => n.code);
                if (a.tache) codes.push(a.tache.code);

                // Pour chaque groupe de prefix : au moins un code du groupe doit matcher
                return Object.values(byPrefix).every(groupCodes =>
                    groupCodes.some(fc => codes.includes(fc))
                );
            });
        }

        return pool;
    }

    // ─── Recherche d'actions ────────────────────────────────────────────────────

    _applySearch(query) {
        let filtered = this._getFilteredPool();

        if (query) {
            filtered = filtered.filter(a => a.label.toLowerCase().includes(query));
            // Trier : starts-with en premier, puis alphabétique
            filtered.sort((a, b) => {
                const aS = a.label.toLowerCase().startsWith(query);
                const bS = b.label.toLowerCase().startsWith(query);
                if (aS && !bS) return -1;
                if (!aS && bS) return 1;
                return a.label.localeCompare(b.label, 'fr');
            });
        } else {
            filtered.sort((a, b) => a.label.localeCompare(b.label, 'fr'));
        }

        this.showDropdown(filtered);
    }

    onActionSearchFocus() {
        const query = this.hasActionSearchTarget ? this.actionSearchTarget.value.toLowerCase().trim() : '';
        this._applySearch(query);
    }

    onActionSearchInput(event) {
        const query = event.target.value.toLowerCase().trim();
        this._applySearch(query);
    }

    onActionSearchBlur() {
        // Délai pour permettre le clic sur un item du dropdown
        setTimeout(() => this.hideDropdown(), 200);
    }

    showDropdown(actions) {
        const dropdown = this.actionDropdownTarget;
        dropdown.innerHTML = '';

        if (actions.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'action-dropdown-empty';
            empty.textContent = 'Aucune action trouvée';
            dropdown.appendChild(empty);
        } else {
            const grid = document.createElement('div');
            grid.className = 'action-dropdown-grid';

            actions.slice(0, 16).forEach(action => {
                const item = document.createElement('div');
                item.className = 'action-dropdown-picto-item';
                item.title = action.label;
                item.appendChild(this._buildPictoCard(action));
                item.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    this.addAction(action);
                    this.actionSearchTarget.value = '';
                    this.hideDropdown();
                });
                grid.appendChild(item);
            });

            dropdown.appendChild(grid);
        }

        dropdown.style.display = 'block';
    }

    hideDropdown() {
        if (this.hasActionDropdownTarget) {
            this.actionDropdownTarget.style.display = 'none';
        }
    }

    addAction(action) {
        if (this.addedActions.some(a => a.actionsId === action.id)) return;
        this.addedActions.push({
            actionsId: action.id,
            label: action.label,
            tache: action.tache || null,
            necessaires: action.necessaires || [],
            meo: action.meo || null,
            suppInterPositions: [],
        });
        // Réinitialiser les filtres après sélection d'une action
        this.activeFilters = [];
        this.renderFilterSelects();
        this.renderActionsList();
        this.serialize();
    }

    removeAction(actionsId) {
        this.addedActions = this.addedActions.filter(a => a.actionsId !== actionsId);
        this.renderActionsList();
        this.serialize();
    }

    toggleActionSupport(actionsId, position) {
        const action = this.addedActions.find(a => a.actionsId === actionsId);
        if (!action) return;
        const idx = action.suppInterPositions.indexOf(position);
        if (idx >= 0) {
            action.suppInterPositions.splice(idx, 1);
        } else {
            action.suppInterPositions.push(position);
        }
        this.serialize();
    }

    // ─── Rendu de la liste des actions ─────────────────────────────────────────

    renderActionsList() {
        if (!this.hasActionsListTarget) return;
        const list = this.actionsListTarget;
        list.innerHTML = '';

        if (this.addedActions.length === 0) {
            return;
        }

        const sortedSelected = [...this.selectedSupports].sort((a, b) => a.orderPosition - b.orderPosition);

        this.addedActions.forEach(action => {
            const row = document.createElement('div');
            row.className = 'action-row';

            // En-tête de l'action : carte picto
            const header = document.createElement('div');
            header.className = 'action-row-header';

            header.appendChild(this._buildPictoCard(action));

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'action-remove-btn';
            removeBtn.title = 'Retirer cette action';
            removeBtn.textContent = '✕';
            removeBtn.addEventListener('click', () => this.removeAction(action.actionsId));
            header.appendChild(removeBtn);
            row.appendChild(header);

            // Checkboxes par support sélectionné
            const checksDiv = document.createElement('div');
            checksDiv.className = 'action-support-checks';

            const assocLabel = document.createElement('span');
            assocLabel.className = 'action-associate-label';
            assocLabel.textContent = 'Associer à :';
            checksDiv.appendChild(assocLabel);

            sortedSelected.forEach(sel => {
                const label = document.createElement('label');
                label.className = 'action-support-check';

                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.checked = action.suppInterPositions.includes(sel.orderPosition);
                cb.addEventListener('change', () => {
                    this.toggleActionSupport(action.actionsId, sel.orderPosition);
                });

                label.appendChild(cb);
                label.appendChild(document.createTextNode(' ' + sel.nom + ' (pos.' + sel.orderPosition + ')'));
                checksDiv.appendChild(label);
            });

            row.appendChild(checksDiv);
            list.appendChild(row);
        });
    }

    // ─── Rendu carte picto action ───────────────────────────────────────────────

    _buildPictoCard(action) {
        const card = document.createElement('div');
        card.className = 'action-picto-card';

        // Section haute : nom tâche + picto tâche
        const top = document.createElement('div');
        top.className = 'action-picto-top';

        if (action.tache) {
            const nom = document.createElement('div');
            nom.className = 'action-picto-tache-nom';
            nom.textContent = action.tache.nom;
            top.appendChild(nom);

            const img = document.createElement('img');
            img.src = this.pictoUrlValue + action.tache.code + '.png';
            img.onerror = () => { img.style.display = 'none'; };
            img.className = 'action-picto-tache-img';
            top.appendChild(img);
        } else {
            const nom = document.createElement('div');
            nom.className = 'action-picto-tache-nom';
            nom.textContent = action.label;
            top.appendChild(nom);
        }
        card.appendChild(top);

        // Section basse : grille nécessaires + carte MEO
        const bottom = document.createElement('div');
        bottom.className = 'action-picto-bottom';

        const grid = document.createElement('div');
        grid.className = 'action-picto-grid';
        (action.necessaires || []).forEach(nec => {
            const filename = nec.type_nom === 'consommable' ? '_' + nec.code : nec.code;
            const img = document.createElement('img');
            img.src = this.pictoUrlValue + filename + '.png';
            img.onerror = () => { img.style.display = 'none'; };
            img.className = 'action-picto-nec-img';
            grid.appendChild(img);
        });
        bottom.appendChild(grid);

        if (action.meo && action.meo.produit_code && action.meo.produit_code !== 'P.00') {
            bottom.appendChild(this._buildMeoCard(action.meo));
        }

        card.appendChild(bottom);
        return card;
    }

    _buildMeoCard(meo) {
        const colorMap = {
            'vert': '#22c55e', 'rouge': '#ef4444', 'bleu': '#3b82f6',
            'jaune': '#f4d40b', 'orange': '#fb923c', 'violet': '#a855f7',
            'rose': '#ec4899', 'noir': '#111827', 'blanc': '#ffffff',
            'gris': '#9ca3af', 'marron': '#92400e',
        };
        let bg = '#ffffff';
        if (meo.produit_couleur) {
            const c = meo.produit_couleur.toLowerCase().trim();
            bg = c.startsWith('#') ? c : (colorMap[c] || '#ffffff');
        }

        const div = document.createElement('div');
        div.style.cssText = 'margin-left:6px;width:70px;border:1px solid #4b5563;border-radius:4px;overflow:hidden;background:#fff;flex-shrink:0;';

        const contenantImg = meo.contenant_id
            ? `<img src="${this.contenantUrlValue}${meo.contenant_id}.png" onerror="this.style.display='none'" style="max-width:20px;max-height:20px;">`
            : '';
        const moyenImg = meo.moyen_dosage_id
            ? `<img src="${this.moyenDosageUrlValue}${meo.moyen_dosage_id}.png" onerror="this.style.display='none'" style="max-width:24px;max-height:24px;">`
            : '';
        const tcImg = meo.temps_contact_id
            ? `<img src="${this.tempsContactUrlValue}${meo.temps_contact_id}.png" onerror="this.style.display='none'" style="max-width:19px;max-height:19px;">`
            : '';

        div.innerHTML = `
            <div style="display:flex;">
                <div style="width:50%;padding:4px;border-right:1px solid #4b5563;background:${bg};display:flex;flex-direction:column;justify-content:space-between;align-items:center;">
                    ${contenantImg}
                    <span style="font-size:8px;font-weight:700;text-align:center;color:#111;">${meo.produit_code || ''}${meo.moyen_dosage_code ? '.' + meo.moyen_dosage_code : ''}</span>
                </div>
                <div style="width:50%;padding:4px;display:flex;flex-direction:column;justify-content:center;align-items:center;">
                    <span style="font-size:7px;color:#6b7280;">Eau</span>
                    <span style="font-size:8px;font-weight:700;">${meo.volume_eau || ''}</span>
                </div>
            </div>
            <div style="display:flex;border-top:1px solid #4b5563;">
                <div style="width:50%;padding:4px;border-right:1px solid #4b5563;display:flex;align-items:center;justify-content:center;">${moyenImg}</div>
                <div style="width:50%;display:flex;flex-direction:column;">
                    <div style="flex:1;padding:4px;display:flex;align-items:center;justify-content:center;">
                        <span style="font-size:8px;font-weight:700;">${meo.volume_produit != null ? meo.volume_produit : ''}</span>
                    </div>
                    <div style="flex:1;padding:4px;display:flex;align-items:center;justify-content:center;">${tcImg}</div>
                </div>
            </div>`;
        return div;
    }

    // ─── Sérialisation ─────────────────────────────────────────────────────────

    serialize() {
        if (!this.hasHiddenDataTarget) return;

        const data = {
            supports: this.selectedSupports
                .sort((a, b) => a.orderPosition - b.orderPosition)
                .map(s => ({
                    support_client_id: s.supportClientId,
                    order_position: s.orderPosition,
                })),
            actions: this.addedActions.map(a => ({
                actions_id: a.actionsId,
                supp_inter_positions: [...a.suppInterPositions],
            })),
        };

        this.hiddenDataTarget.value = JSON.stringify(data);
    }

    // ─── Visibilité des sections ────────────────────────────────────────────────

    showSupportsSection() {
        if (this.hasSupportsSectionTarget) {
            this.supportsSectionTarget.style.display = '';
        }
    }

    showActionsSection() {
        if (this.hasActionsSectionTarget) {
            this.actionsSectionTarget.style.display = '';
        }
    }

    hideActionsSection() {
        if (this.hasActionsSectionTarget) {
            this.actionsSectionTarget.style.display = 'none';
        }
    }

    hideSections() {
        if (this.hasSupportsSectionTarget) {
            this.supportsSectionTarget.style.display = 'none';
            if (this.hasSupportsGridTarget) this.supportsGridTarget.innerHTML = '';
        }
        this.hideActionsSection();
        if (this.hasActionsListTarget) this.actionsListTarget.innerHTML = '';
    }
}
