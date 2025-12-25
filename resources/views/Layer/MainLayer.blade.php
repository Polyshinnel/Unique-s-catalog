<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/unique-favicon.png">
    <link rel="apple-touch-icon" href="assets/img/unique-favicon.png">
    <title>@yield('title', 'Каталог Юник С')</title>
    
    @hasSection('meta')
        @yield('meta')
    @else
        <!-- Мета-теги по умолчанию для каталога -->
        <meta name="description" content="Продажа бывшего в употреблении промышленного оборудования: металлорежущих и деревообрабатывающих станков, прессового и кузнечного оборудования, спецтехники и оборудования для погрузочно-разгрузочных работ">
        
        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="Каталог ЮНИК С">
        <meta property="og:description" content="Продажа бывшего в употреблении промышленного оборудования: металлорежущих и деревообрабатывающих станков, прессового и кузнечного оборудования, спецтехники и оборудования для погрузочно-разгрузочных работ">
        <meta property="og:image" content="{{ url('/assets/img/catalog.jpeg') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        
        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Каталог ЮНИК С">
        <meta name="twitter:description" content="Продажа бывшего в употреблении промышленного оборудования: металлорежущих и деревообрабатывающих станков, прессового и кузнечного оборудования, спецтехники и оборудования для погрузочно-разгрузочных работ">
        <meta name="twitter:image" content="{{ url('/assets/img/catalog.jpeg') }}">
    @endif
</head>
<body>
    <header class="header">
        <div class="box-container">
            <!-- Десктопная версия -->
            <div class="header-desktop">
                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Открыть меню">☰</button>

                <div class="header-content">
                    <div class="logo">
                        <img src="assets/img/unique-logo.png" alt="Unique Logo">
                    </div>
                    
                    <nav class="menu" id="mainMenu">
                        <a href="https://uniqset.com/" class="menu-link">Главная</a>
                        <a href="https://uniqset.com/prodazha-oborudovaniya/" class="menu-link">Услуги</a>
                        <a href="/" class="menu-link">Каталог оборудования</a>
                        <a href="https://uniqset.com/otgruzki/" class="menu-link">Отгрузки</a>
                        <a href="https://uniqset.com/o-nas/" class="menu-link">Компания</a>
                        <a href="https://uniqset.com/o-nas/contacts/" class="menu-link">Контакты</a>
                    </nav>
                    
                    <div class="header-right">
                        <a href="https://vk.com/uniqset" class="vk-link" target="_blank">
                            <img src="assets/img/vk-logo.svg" alt="VK Icon" class="vk-icon">
                        </a>
                        
                        <div class="contact-info">
                            <div class="contact-item">
                                <span class="contact-icon phone-icon"></span>
                                <span class="contact-text">8 (4842) 59-65-75</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-icon email-icon"></span>
                                <span class="contact-text">info@uniqset.com</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Мобильная версия -->
            <div class="header-mobile">
                <div class="header-content">
                    <div class="header-top">
                        <div class="logo">
                            <img src="assets/img/unique-logo.png" alt="Unique Logo">
                        </div>
                        
                        <div class="header-right">
                            <a href="https://vk.com/uniqset" class="vk-link" target="_blank">
                                <img src="assets/img/vk-logo.svg" alt="VK Icon" class="vk-icon">
                            </a>
                            
                            <button class="mobile-menu-toggle" id="mobileMenuToggleMobile" aria-label="Открыть меню">☰</button>
                        </div>
                    </div>
                    
                    <div class="header-bottom">
                        <div class="contact-info">
                            <div class="contact-item">
                                <span class="contact-icon phone-icon"></span>
                                <span class="contact-text">8 (4842) 59-65-75</span>
                            </div>
                            <div class="contact-item">
                                <span class="contact-icon email-icon"></span>
                                <span class="contact-text">info@uniqset.com</span>
                            </div>
                        </div>
                        
                        <button class="header-callback-button" id="headerCallbackButton">Обратный звонок</button>
                    </div>
                    
                    <nav class="menu" id="mainMenuMobile">
                        <a href="https://uniqset.com/" class="menu-link">Главная</a>
                        <a href="https://uniqset.com/prodazha-oborudovaniya/" class="menu-link">Услуги</a>
                        <a href="/" class="menu-link">Каталог оборудования</a>
                        <a href="https://uniqset.com/otgruzki/" class="menu-link">Отгрузки</a>
                        <a href="https://uniqset.com/o-nas/" class="menu-link">Компания</a>
                        <a href="https://uniqset.com/o-nas/contacts/" class="menu-link">Контакты</a>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="box-container">
            <div class="footer-content">
                <div class="footer-content-blocks">
                    <div class="footer-content-block-item">
                        <img src="assets/img/unique-logo.png" alt="" class="footer-logo">
                        <p>ООО “Юник С”</p>
                        <p>ИНН: 4027139409</p>
                        <p>ОГРН: 1194027002861</p>
                    </div>

                    <div class="footer-content-block-item">
                        <h2>Услуги</h2>
                        <ul>
                            <li><a href="https://uniqset.com/prodazha-oborudovaniya/">Продажа оборудования</a></li>
                            <li><a href="https://uniqset.com/vykup/">Выкуп оборудования</a></li>
                            <li><a href="https://uniqset.com/prodazha-instrumenta/">Продажа инструмента</a></li>
                            <li><a href="https://uniqset.com/import/">Импорт оборудования</a></li>
                        </ul>
                    </div>

                    <div class="footer-content-block-item">
                        <h2>Контакты</h2>

                        <div class="contact-item">
                            <span class="contact-icon phone-icon"></span>
                            <span class="contact-text">8 (4842) 59-65-75</span>
                        </div>
                        <div class="contact-item">
                            <span class="contact-icon email-icon"></span>
                            <span class="contact-text">info@uniqset.com</span>
                        </div>

                        <a href="https://vk.com/uniqset" class="vk-link vk-link_footer" target="_blank">
                            <img src="assets/img/vk-logo.svg" alt="VK Icon" class="vk-icon">
                        </a>
                    </div>

                    <div class="footer-content-block-item">
                        <h2>Обратный звонок</h2>

                        <form class="footer-recall-form" id="footerRecallForm">
                            @csrf
                            <div class="form-group">
                                <label for="footer-phone">Телефон</label>
                                <input type="text" name="phone" id="footer-phone" placeholder="Ваш номер телефона" required>
                                <div class="form-error" id="footer-phone-error" style="display: none; color: red; font-size: 12px; margin-top: 5px;"></div>
                            </div>
                            
                            <input type="submit" class="form-submit" value="Позвоните мне" id="footer-submit-btn">
                            <div class="form-success" id="footer-form-success" style="display: none; color: green; font-size: 14px; margin-top: 10px;">Сообщение успешно отправлено!</div>
                        </form>
                    </div>
                </div>
                <p class="copyright">© “ЮНИК С” 2019-2025</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
    <script>
        // Мобильное меню для десктопной версии
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const mainMenu = document.getElementById('mainMenu');

        if (mobileMenuToggle && mainMenu) {
            mobileMenuToggle.addEventListener('click', function() {
                mainMenu.classList.toggle('active');
                this.textContent = mainMenu.classList.contains('active') ? '✕' : '☰';
            });

            // Закрытие меню при клике на ссылку
            const menuLinks = mainMenu.querySelectorAll('.menu-link');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mainMenu.classList.remove('active');
                    mobileMenuToggle.textContent = '☰';
                });
            });

            // Закрытие меню при клике вне его области
            document.addEventListener('click', function(e) {
                if (!mainMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                    mainMenu.classList.remove('active');
                    mobileMenuToggle.textContent = '☰';
                }
            });
        }

        // Мобильное меню для мобильной версии
        const mobileMenuToggleMobile = document.getElementById('mobileMenuToggleMobile');
        const mainMenuMobile = document.getElementById('mainMenuMobile');

        if (mobileMenuToggleMobile && mainMenuMobile) {
            mobileMenuToggleMobile.addEventListener('click', function() {
                mainMenuMobile.classList.toggle('active');
                this.textContent = mainMenuMobile.classList.contains('active') ? '✕' : '☰';
            });

            // Закрытие меню при клике на ссылку
            const menuLinksMobile = mainMenuMobile.querySelectorAll('.menu-link');
            menuLinksMobile.forEach(link => {
                link.addEventListener('click', function() {
                    mainMenuMobile.classList.remove('active');
                    mobileMenuToggleMobile.textContent = '☰';
                });
            });

            // Закрытие меню при клике вне его области
            document.addEventListener('click', function(e) {
                if (!mainMenuMobile.contains(e.target) && !mobileMenuToggleMobile.contains(e.target)) {
                    mainMenuMobile.classList.remove('active');
                    mobileMenuToggleMobile.textContent = '☰';
                }
            });
        }

        // Кнопка обратного звонка в шапке
        const headerCallbackButton = document.getElementById('headerCallbackButton');
        if (headerCallbackButton) {
            headerCallbackButton.addEventListener('click', function() {
                // Прокрутка к форме обратного звонка в футере
                const footerForm = document.querySelector('.footer-recall-form');
                if (footerForm) {
                    footerForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Фокус на поле ввода телефона
                    setTimeout(function() {
                        const phoneInput = document.getElementById('footer-phone');
                        if (phoneInput) {
                            phoneInput.focus();
                        }
                    }, 500);
                }
            });
        }

        // Обработка формы обратного звонка
        const footerRecallForm = document.getElementById('footerRecallForm');
        if (footerRecallForm) {
            footerRecallForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const phoneInput = document.getElementById('footer-phone');
                const errorDiv = document.getElementById('footer-phone-error');
                const successDiv = document.getElementById('footer-form-success');
                const submitBtn = document.getElementById('footer-submit-btn');
                
                // Скрываем предыдущие сообщения
                errorDiv.style.display = 'none';
                successDiv.style.display = 'none';
                errorDiv.textContent = '';
                
                const phone = phoneInput.value.trim();
                
                // Валидация телефона на клиенте
                if (!validateRussianPhone(phone)) {
                    errorDiv.textContent = 'Некорректный номер телефона для РФ. Введите номер в формате +7XXXXXXXXXX или 8XXXXXXXXXX';
                    errorDiv.style.display = 'block';
                    return;
                }
                
                // Блокируем кнопку отправки
                submitBtn.disabled = true;
                submitBtn.value = 'Отправка...';
                
                // Отправляем AJAX запрос
                fetch('{{ route("callback.send") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        phone: phone
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Показываем сообщение об успехе
                        successDiv.style.display = 'block';
                        // Очищаем форму
                        phoneInput.value = '';
                        // Прокручиваем к сообщению об успехе
                        successDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        // Показываем ошибку
                        errorDiv.textContent = data.message || 'Произошла ошибка. Попробуйте позже.';
                        errorDiv.style.display = 'block';
                    }
                })
                .catch(error => {
                    errorDiv.textContent = 'Произошла ошибка при отправке. Попробуйте позже.';
                    errorDiv.style.display = 'block';
                })
                .finally(() => {
                    // Разблокируем кнопку
                    submitBtn.disabled = false;
                    submitBtn.value = 'Позвоните мне';
                });
            });
        }

        // Функция валидации российского номера телефона
        function validateRussianPhone(phone) {
            if (!phone) return false;
            
            // Удаляем все символы кроме цифр и +
            const cleaned = phone.replace(/[^\d+]/g, '');
            
            // Проверяем, что номер начинается с +7, 7 или 8
            if (!/^(\+?7|8)/.test(cleaned)) {
                return false;
            }
            
            // Заменяем 8 на 7 для единообразия
            let normalized = cleaned;
            if (normalized.startsWith('8')) {
                normalized = '7' + normalized.substring(1);
            }
            
            // Удаляем + если есть
            normalized = normalized.replace(/^\+/, '');
            
            // Проверяем, что номер состоит из 11 цифр (7 + 10 цифр)
            if (normalized.length !== 11 || normalized[0] !== '7') {
                return false;
            }
            
            // Проверяем, что вторая цифра (код оператора) от 3 до 9
            const operatorCode = parseInt(normalized[1]);
            if (operatorCode < 3 || operatorCode > 9) {
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>
