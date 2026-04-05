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

                if (!(this instanceof Element)) {
                    // console.error('Lỗi JS (Tabs Event): "this" không phải là một phần tử DOM hợp lệ.');
                    return;
                }

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

                const targetPanelId = this.getAttribute('data-tab-target'); // ĐÃ SỬA
                if (targetPanelId) {
                    const targetPanel = container.querySelector(targetPanelId);
                    if (targetPanel) {
                        targetPanel.classList.add('active');
                        if (targetPanel.classList.contains('tab-content-panel')) targetPanel.classList.add('active-panel');
                    } else {
                        // console.error('Lỗi JS (Tabs): Panel không tìm thấy:', targetPanelId, "cho nút:", this);
                    }
                } else {
                    // console.error('Lỗi JS (Tabs): Nút tab không có data-tab-target:', this);
                }
            });

            if (button instanceof Element && (button.classList.contains('active') || button.classList.contains('active-tab'))) {
                button.setAttribute('aria-selected', 'true');
                const targetId = button.getAttribute('data-tab-target'); // ĐÃ SỬA
                if(targetId){
                    const targetP = container.querySelector(targetId);
                    if(targetP) {
                        targetP.classList.add('active');
                        if (targetP.classList.contains('tab-content-panel')) targetP.classList.add('active-panel');
                    }
                }
            } else if (button instanceof Element) {
                button.setAttribute('aria-selected', 'false');
            }
        });
        
        let isAnyTabMarkedActiveInHTML = false;
        for (let i = 0; i < tabButtons.length; i++) { // Dùng for loop để có thể break
            if (tabButtons[i] instanceof Element && (tabButtons[i].classList.contains('active') || tabButtons[i].classList.contains('active-tab'))) {
                isAnyTabMarkedActiveInHTML = true;
                break; 
            }
        }

        if (!isAnyTabMarkedActiveInHTML && tabButtons.length > 0 && tabButtons[0] instanceof Element) {
            const firstButton = tabButtons[0];
            const firstTargetPanelId = firstButton.getAttribute('data-tab-target'); // ĐÃ SỬA

            if (firstTargetPanelId) {
                const firstTargetPanel = container.querySelector(firstTargetPanelId);
                if (firstTargetPanel) {
                    firstButton.classList.add('active');
                    if (firstButton.classList.contains('tab-link')) firstButton.classList.add('active-tab');
                    firstButton.setAttribute('aria-selected', 'true');

                    firstTargetPanel.classList.add('active');
                    if (firstTargetPanel.classList.contains('tab-content-panel')) firstTargetPanel.classList.add('active-panel');
                } else {
                     // console.error('Lỗi JS (Tabs Init): Panel đầu tiên không tìm thấy:', firstTargetPanelId);
                }
            } else {
                // console.error('Lỗi JS (Tabs Init): Nút tab đầu tiên không có data-tab-target:', firstButton);
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
    } else {
        if (document.getElementById('category-toggle') && !categorySidebar) {
             // console.warn("Cảnh báo JS (Sidebar): Không tìm thấy sidebar #category-sidebar-content (có thể do trang không có sidebar này).");
        }
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
    if (document.querySelector('.build-pc-page')) { 
        
        const componentRows = document.querySelectorAll('.component-table tbody tr[data-component-type]');
        const modal = document.getElementById('product-selection-modal');
        const closeModalButton = modal.querySelector('.build-pc-close-modal');
        const modalTitle = modal.querySelector('#modal-title');
        const modalProductList = modal.querySelector('#modal-product-list');
        const modalSearchKeywordInput = modal.querySelector('#modal-search-keyword');

        const buildSummaryList = document.getElementById('build-summary-list');
        const buildTotalPriceElement = document.getElementById('build-total-price');
        const btnAddBuildToCart = document.getElementById('btn-add-build-to-cart');
        const addToCartMessage = document.getElementById('add-to-cart-message');

        let currentSelectedComponents = {}; 
        let currentEditingComponentType = null; 
        let currentIdLoaiForModal = null;

        function updateSummaryAndTotal() {
            buildSummaryList.innerHTML = ''; 
            let totalPrice = 0;
            let componentCount = 0;

            Object.keys(currentSelectedComponents).forEach(type => {
                const component = currentSelectedComponents[type];
                if (component) {
                    const summaryItem = document.createElement('div');
                    summaryItem.style.display = 'flex';
                    summaryItem.style.alignItems = 'center';
                    summaryItem.style.marginBottom = '8px';
                    summaryItem.style.fontSize = '0.9em';

                    const img = document.createElement('img');
                    img.src = component.image || 'https://via.placeholder.com/40';
                    img.alt = component.name;
                    img.style.width = '40px';
                    img.style.height = '40px';
                    img.style.objectFit = 'contain';
                    img.style.marginRight = '10px';
                    img.style.border = '1px solid #eee';

                    const nameDiv = document.createElement('div');
                    nameDiv.textContent = component.name;
                    nameDiv.style.flexGrow = '1';

                    const priceDiv = document.createElement('div');
                    priceDiv.textContent = parseInt(component.price).toLocaleString('vi-VN') + '₫';
                    priceDiv.style.fontWeight = '500';
                    
                    summaryItem.appendChild(img);
                    summaryItem.appendChild(nameDiv);
                    summaryItem.appendChild(priceDiv);
                    buildSummaryList.appendChild(summaryItem);
                    
                    totalPrice += parseInt(component.price);
                    componentCount++;
                }
            });

            if (componentCount === 0) {
                buildSummaryList.innerHTML = '<p style="color:#777; text-align:center;"><em>Chọn linh kiện để xem tóm tắt.</em></p>';
            }

            buildTotalPriceElement.textContent = totalPrice.toLocaleString('vi-VN') + '₫';
            btnAddBuildToCart.disabled = componentCount === 0;
            addToCartMessage.style.display = componentCount === 0 ? 'block' : 'none';
        }

        function fetchAndDisplayProducts(idLoai, componentName, keyword = '') {
            currentIdLoaiForModal = idLoai; 
            modalTitle.textContent = 'Chọn ' + componentName;
            modalProductList.innerHTML = '<p>Đang tải sản phẩm...</p>';
            modal.style.display = 'block';
            modalSearchKeywordInput.value = keyword; 

            fetch(`api/get_products_for_builder.php?id_loai=${encodeURIComponent(idLoai)}&keyword=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    modalProductList.innerHTML = ''; 
                    if (data.success && data.products.length > 0) {
                        data.products.forEach(product => {
                            const itemDiv = document.createElement('div');
                            itemDiv.className = 'product-item';
                            itemDiv.innerHTML = `
                                <img src="${product.hinh_anh ? product.hinh_anh : 'https://via.placeholder.com/60'}" alt="${product.ten_sp}">
                                <div class="info">
                                    <div class="name">${product.ten_sp} ${product.ten_thuonghieu ? '('+product.ten_thuonghieu+')' : ''}</div>
                                    <div class="price">${parseInt(product.gia).toLocaleString('vi-VN')}₫</div>
                                </div>
                                <button class="btn-select-this-product" data-id="${product.id_sanpham}" data-name="${product.ten_sp}" data-price="${product.gia}" data-image="${product.hinh_anh || ''}">Chọn</button>
                            `;
                            modalProductList.appendChild(itemDiv);
                        });
                    } else {
                        modalProductList.innerHTML = `<p>${data.message || 'Không có sản phẩm nào phù hợp.'}</p>`;
                    }
                })
                .catch(error => {
                    console.error('Lỗi khi tải sản phẩm cho modal:', error);
                    modalProductList.innerHTML = '<p>Không thể tải danh sách sản phẩm. Vui lòng thử lại.</p>';
                });
        }
        
        modalSearchKeywordInput.addEventListener('input', function() {
            if(currentIdLoaiForModal && currentEditingComponentType){
                 const componentRow = document.querySelector(`tr[data-component-type="${currentEditingComponentType}"]`);
                 if(componentRow) {
                    fetchAndDisplayProducts(currentIdLoaiForModal, componentRow.dataset.componentName, this.value);
                 }
            }
        });

        componentRows.forEach(row => {
            const chooseButton = row.querySelector('.btn-choose-component');
            const removeButton = row.querySelector('.btn-remove-component');
            const nameDisplay = row.querySelector('.selected-component-name .name-display');
            const imageDisplay = row.querySelector('.selected-component-name .selected-component-image');
            const priceDisplay = row.querySelector('.selected-component-price');
            const componentType = row.dataset.componentType;

            chooseButton.addEventListener('click', function() {
                currentEditingComponentType = componentType;
                const idLoai = row.dataset.idLoai;
                const componentName = row.dataset.componentName;
                fetchAndDisplayProducts(idLoai, componentName);
            });

            removeButton.addEventListener('click', function() {
                delete currentSelectedComponents[componentType];
                nameDisplay.textContent = 'Chưa chọn';
                imageDisplay.src = '';
                imageDisplay.style.display = 'none';
                priceDisplay.textContent = '0₫';
                this.style.display = 'none';
                chooseButton.innerHTML = `<i class="fa-solid fa-plus"></i> Chọn`;
                updateSummaryAndTotal();
            });
        });

        if(closeModalButton) { // Kiểm tra nút đóng tồn tại
            closeModalButton.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        if(modalProductList){ // Kiểm tra modal product list tồn tại
            modalProductList.addEventListener('click', function(event) {
                if (event.target.classList.contains('btn-select-this-product')) {
                    const selectedButton = event.target;
                    const productId = selectedButton.dataset.id;
                    const productName = selectedButton.dataset.name;
                    const productPrice = selectedButton.dataset.price;
                    const productImage = selectedButton.dataset.image;

                    currentSelectedComponents[currentEditingComponentType] = {
                        id: productId,
                        name: productName,
                        price: productPrice,
                        image: productImage
                    };

                    const targetRow = document.querySelector(`tr[data-component-type="${currentEditingComponentType}"]`);
                    if (targetRow) {
                        targetRow.querySelector('.selected-component-name .name-display').textContent = productName;
                        const imgEl = targetRow.querySelector('.selected-component-name .selected-component-image');
                        imgEl.src = productImage || 'https://via.placeholder.com/40';
                        imgEl.alt = productName;
                        imgEl.style.display = productImage ? 'inline-block' : 'none';
                        targetRow.querySelector('.selected-component-price').textContent = parseInt(productPrice).toLocaleString('vi-VN') + '₫';
                        targetRow.querySelector('.btn-choose-component').innerHTML = `<i class="fa-solid fa-arrows-rotate"></i> Thay đổi`;
                        targetRow.querySelector('.btn-remove-component').style.display = 'inline-block';
                    }
                    
                    modal.style.display = 'none';
                    updateSummaryAndTotal();
                }
            });
        }

        if(btnAddBuildToCart){ // Kiểm tra nút add to cart tồn tại
            btnAddBuildToCart.addEventListener('click', function() {
                const itemsToAdd = [];
                Object.keys(currentSelectedComponents).forEach(type => {
                    if (currentSelectedComponents[type]) {
                        itemsToAdd.push({
                            product_id: currentSelectedComponents[type].id,
                            quantity: 1 
                        });
                    }
                });

                if (itemsToAdd.length > 0) {
                    fetch('add_multiple_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ items: itemsToAdd })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = 'cart.php'; 
                        } else {
                            alert('Lỗi khi thêm cấu hình vào giỏ: ' + (data.message || 'Vui lòng thử lại.'));
                        }
                    })
                    .catch(error => {
                        console.error('Lỗi khi thêm cấu hình vào giỏ:', error);
                        alert('Có lỗi xảy ra, không thể thêm cấu hình vào giỏ.');
                    });
                }
            });
        }
        if (typeof updateSummaryAndTotal === "function") { 
            updateSummaryAndTotal();
        }
    }
    

}); 