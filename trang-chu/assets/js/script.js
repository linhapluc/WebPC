document.addEventListener('DOMContentLoaded', function() {

    // --- CODE XỬ LÝ TABS (ĐÃ SỬA ĐÚNG data-tab-target) ---
    const allTabContainers = document.querySelectorAll('.tabbed-products, .product-full-details');

    allTabContainers.forEach(container => {
        const tabButtons = container.querySelectorAll('.tab-link, .detail-tab-btn');
        const tabPanels = container.querySelectorAll('.tab-content-panel, .detail-panel');

        if (tabButtons.length === 0 || tabPanels.length === 0) {
            return;
        }

        tabButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                if (!(this instanceof Element)) { return; }
                tabButtons.forEach(btn => {
                    btn.classList.remove('active', 'active-tab');
                    btn.setAttribute('aria-selected', 'false');
                });
                tabPanels.forEach(panel => {
                    panel.classList.remove('active', 'active-panel');
                });
                this.classList.add('active');
                if (this.classList.contains('tab-link')) this.classList.add('active-tab');
                this.setAttribute('aria-selected', 'true');
                const targetPanelId = this.getAttribute('data-tab-target');
                if (targetPanelId) {
                    const targetPanel = container.querySelector(targetPanelId);
                    if (targetPanel) {
                        targetPanel.classList.add('active');
                        if (targetPanel.classList.contains('tab-content-panel')) targetPanel.classList.add('active-panel');
                    }
                }
            });

            if (button instanceof Element && (button.classList.contains('active') || button.classList.contains('active-tab'))) {
                button.setAttribute('aria-selected', 'true');
                const targetId = button.getAttribute('data-tab-target');
                if (targetId) {
                    const targetP = container.querySelector(targetId);
                    if (targetP) {
                        targetP.classList.add('active');
                        if (targetP.classList.contains('tab-content-panel')) targetP.classList.add('active-panel');
                    }
                }
            } else if (button instanceof Element) {
                button.setAttribute('aria-selected', 'false');
            }
        });

        let isAnyTabMarkedActiveInHTML = false;
        for (let i = 0; i < tabButtons.length; i++) {
            if (tabButtons[i] instanceof Element && (tabButtons[i].classList.contains('active') || tabButtons[i].classList.contains('active-tab'))) {
                isAnyTabMarkedActiveInHTML = true;
                break;
            }
        }

        if (!isAnyTabMarkedActiveInHTML && tabButtons.length > 0 && tabButtons[0] instanceof Element) {
            const firstButton = tabButtons[0];
            const firstTargetPanelId = firstButton.getAttribute('data-tab-target');
            if (firstTargetPanelId) {
                const firstTargetPanel = container.querySelector(firstTargetPanelId);
                if (firstTargetPanel) {
                    firstButton.classList.add('active');
                    if (firstButton.classList.contains('tab-link')) firstButton.classList.add('active-tab');
                    firstButton.setAttribute('aria-selected', 'true');
                    firstTargetPanel.classList.add('active');
                    if (firstTargetPanel.classList.contains('tab-content-panel')) firstTargetPanel.classList.add('active-panel');
                }
            }
        }
    });
    // --- KẾT THÚC CODE XỬ LÝ TABS ---

    // --- CODE XỬ LÝ SIDEBAR ---
    const categoryToggleButton = document.getElementById('category-toggle');
    const categorySidebar = document.getElementById('category-sidebar-content');
    if (categoryToggleButton && categorySidebar) {
        categoryToggleButton.addEventListener('click', function() {
            categorySidebar.classList.toggle('is-visible');
            categoryToggleButton.setAttribute('aria-expanded', categorySidebar.classList.contains('is-visible'));
        });
        document.addEventListener('click', function(event) {
            if (categorySidebar.classList.contains('is-visible') &&
                !categorySidebar.contains(event.target) &&
                !categoryToggleButton.contains(event.target)) {
                categorySidebar.classList.remove('is-visible');
                categoryToggleButton.setAttribute('aria-expanded', 'false');
            }
        });
    }
    // --- KẾT THÚC CODE XỬ LÝ SIDEBAR ---

    // --- CODE XỬ LÝ DROPDOWN TÀI KHOẢN ---
    const dropdownTriggers = document.querySelectorAll('.dropdown-trigger');
    dropdownTriggers.forEach(trigger => {
        const menu = trigger.nextElementSibling;
        if (menu && menu.classList.contains('dropdown-menu-content')) {
            trigger.addEventListener('click', function(event) {
                event.preventDefault();
                const currentlyActiveMenu = document.querySelector('.dropdown-menu-content.active');
                if (currentlyActiveMenu && currentlyActiveMenu !== menu) {
                    currentlyActiveMenu.classList.remove('active');
                    const otherTrigger = currentlyActiveMenu.previousElementSibling;
                    if (otherTrigger && otherTrigger.classList.contains('dropdown-trigger')) {
                        otherTrigger.setAttribute('aria-expanded', 'false');
                    }
                }
                menu.classList.toggle('active');
                trigger.setAttribute('aria-expanded', menu.classList.contains('active'));
            });
        }
    });
    document.addEventListener('click', function(event) {
        document.querySelectorAll('.dropdown-container').forEach(container => {
            const trigger = container.querySelector('.dropdown-trigger');
            const menu = container.querySelector('.dropdown-menu-content.active');
            if (menu && trigger && !trigger.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.remove('active');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    });
    // --- KẾT THÚC CODE XỬ LÝ DROPDOWN ---

    // --- CODE XỬ LÝ NÚT +/- CHO SỐ LƯỢNG SẢN PHẨM (TRANG CHI TIẾT) ---
    const quantityDetailBoxes = document.querySelectorAll('.product-purchase-section .quantity-input');
    quantityDetailBoxes.forEach((box) => {
        let input = box.querySelector('input[type="number"]');
        const btnMinus = box.querySelector('.qty-btn.minus');
        const btnPlus = box.querySelector('.qty-btn.plus');

        if (input && btnMinus && btnPlus) {
            btnMinus.addEventListener('click', function() {
                if (this.disabled) return;
                let val = parseInt(input.value);
                let min = parseInt(input.min);
                if (isNaN(min) || (input.hasAttribute('min') && input.min === "")) min = 0;
                if (val > min) {
                    input.value = val - 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
            btnPlus.addEventListener('click', function() {
                if (this.disabled) return;
                let val = parseInt(input.value);
                let max = parseInt(input.max);
                if (isNaN(max) || (input.hasAttribute('max') && input.max === "") || val < max) {
                    input.value = val + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
            input.addEventListener('change', function() {
                let val = parseInt(input.value);
                let min = parseInt(input.min);
                if (isNaN(min) || (input.hasAttribute('min') && input.min === "")) min = 0;
                let max = parseInt(input.max);

                if (input.value === "" || isNaN(val) || val < min) {
                    input.value = min;
                } else if (!isNaN(max) && !(input.hasAttribute('max') && input.max === "") && val > max) {
                    input.value = max;
                }
            });
            input.addEventListener('blur', function() {
                if (input.value === '') {
                    let min = parseInt(input.min);
                    if (isNaN(min) || (input.hasAttribute('min') && input.min === "")) min = 0;
                    input.value = min;
                }
            });
        }
    });
    // --- KẾT THÚC CODE XỬ LÝ NÚT +/- ---

    // --- CODE XỬ LÝ TRANG XÂY DỰNG CẤU HÌNH (build_pc.php) ---
   

    /* ========================================= */
    /* ===    LOGIC XỬ LÝ DARK MODE          === */
    /* ========================================= */
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        const icon = themeToggleBtn.querySelector('i');

        const updateIcon = () => {
            if (document.body.classList.contains('dark-mode')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        };

        updateIcon();

        themeToggleBtn.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            document.documentElement.classList.toggle('dark-mode');
            let theme = 'light';
            if (document.body.classList.contains('dark-mode')) {
                theme = 'dark';
            }
            localStorage.setItem('theme', theme);
            updateIcon();
        });
    }
});