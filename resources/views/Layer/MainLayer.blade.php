<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Varela+Round&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/unique-favicon.png">
    <link rel="apple-touch-icon" href="assets/img/unique-favicon.png">
    <title>Каталог Юник-с</title>
</head>
<body>
    <header class="header">
        <div class="box-container">
            <div class="header-content">
                <div class="logo">
                    <img src="assets/img/unique-logo.png" alt="Unique Logo">
                </div>
                
                <nav class="menu">
                    <a href="#" class="menu-link">Главная</a>
                    <a href="#" class="menu-link">Услуги</a>
                    <a href="#" class="menu-link">Интернет магазин</a>
                    <a href="#" class="menu-link">Отгрузки</a>
                    <a href="#" class="menu-link">Компания</a>
                    <a href="#" class="menu-link">Контакты</a>
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
                            <li><a href="#">Продажа оборудования</a></li>
                            <li><a href="#">Выкуп оборудования</a></li>
                            <li><a href="#">Продажа инструмента</a></li>
                            <li><a href="#">Импорт оборудования</a></li>
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

                        <form class="footer-recall-form">
                            <div class="form-group">
                                <label for="footer-phone">Телефон</label>
                                <input type="text" name="footer-phone" id="footer-phone" placeholder="Ваш номер телефона">
                            </div>
                            
                            <input type="submit" class="form-submit" value="Позвоните мне">
                        </form>
                    </div>
                </div>
                <p class="copyright">© “ЮНИК С” 2019-2025</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
