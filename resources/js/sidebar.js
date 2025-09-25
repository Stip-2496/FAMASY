document.addEventListener('DOMContentLoaded', function() {
    initSidebarState();
    setupSidebarListeners();
    setupLivewireNavigationHandlers();
});

function initSidebarState() {
    const currentPath = window.location.pathname;
    clearAllActiveStates();

    const allLinks = [...document.querySelectorAll('.quick-access a, .submenu a')];
    let activeLink = null;

    // Buscar mejor coincidencia con la URL actual
    allLinks.forEach(link => {
        try {
            const linkPath = new URL(link.href).pathname;
            if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
                if (!activeLink || linkPath.length > new URL(activeLink.href).pathname.length) {
                    activeLink = link;
                }
            }
        } catch (e) {
            console.error("Error processing link:", e);
        }
    });

    if (activeLink) {
        if (activeLink.closest('.quick-access')) {
            activeLink.classList.add('active');
        } else {
            activeLink.classList.add('active');
            const submenu = activeLink.closest('.submenu');
            if (submenu) {
                const moduleButton = submenu.previousElementSibling;
                if (moduleButton?.classList.contains('module-button')) {
                    moduleButton.classList.add('active', 'has-active-child');
                    submenu.classList.add('open');
                    rotateArrow(moduleButton, true);
                }
            }
        }
    }
}

function setupSidebarListeners() {
    // Acceso rápido
    document.querySelectorAll('.quick-access a').forEach(link => {
        link.addEventListener('click', function() {
            if (!this.classList.contains('active')) {
                clearAllActiveStates();
                this.classList.add('active');
                closeAllSubmenus();
            }
        });
    });

    // Botones de módulo
    document.addEventListener('click', function(e) {
        const moduleButton = e.target.closest('.module-button');
        if (moduleButton) {
            e.preventDefault();
            e.stopPropagation();

            const submenu = moduleButton.nextElementSibling;
            if (submenu?.classList.contains('submenu')) {
                if (!submenu.classList.contains('open')) {
                    closeAllSubmenus();
                }

                submenu.classList.toggle('open');

                // Si tiene hijo activo, conservar has-active-child aunque esté cerrado
                if (submenu.querySelector('a.active')) {
                    moduleButton.classList.add('has-active-child');
                } else {
                    moduleButton.classList.toggle('active', submenu.classList.contains('open'));
                    moduleButton.classList.remove('has-active-child');
                }

                rotateArrow(moduleButton, submenu.classList.contains('open'));
            }
        }
    });

    // Enlaces de submódulo
    document.querySelectorAll('.submenu a').forEach(link => {
        link.addEventListener('click', function() {
            clearAllActiveStates();
            this.classList.add('active');

            const submenu = this.closest('.submenu');
            const moduleButton = submenu?.previousElementSibling;
            if (moduleButton?.classList.contains('module-button')) {
                moduleButton.classList.add('active', 'has-active-child');
                submenu.classList.add('open');
                rotateArrow(moduleButton, true);
            }
        });
    });
}

function setupLivewireNavigationHandlers() {
    document.addEventListener('livewire:navigated', initSidebarState);
}

function clearAllActiveStates() {
    document.querySelectorAll(
        '.quick-access a.active, ' +
        '.module-button.active, ' +
        '.module-button.has-active-child, ' +
        '.submenu a.active'
    ).forEach(el => {
        el.classList.remove('active', 'has-active-child');
    });
}

function closeAllSubmenus() {
    document.querySelectorAll('.submenu.open').forEach(el => {
        el.classList.remove('open');
    });

    document.querySelectorAll('.module-button .arrow-icon').forEach(el => {
        el.style.transform = 'rotate(0deg)';
    });
}

function rotateArrow(moduleButton, isOpen) {
    const arrow = moduleButton.querySelector('.arrow-icon');
    if (arrow) {
        arrow.style.transform = isOpen ? 'rotate(90deg)' : 'rotate(0deg)';
    }
}