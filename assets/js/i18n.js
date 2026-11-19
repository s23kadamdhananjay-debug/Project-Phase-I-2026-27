(function() {
    "use strict";

    const scriptUrl = document.currentScript ? document.currentScript.src : window.location.href;
    const languageDirectory = new URL("../lang/", scriptUrl);
    const originalText = new WeakMap();
    const originalAttributes = new WeakMap();
    const originalTitle = document.title;
    let dictionaries = { en: {}, hi: {} };
    let currentLanguage = "en";
    let observer = null;

    function savedLanguage() {
        try {
            return window.localStorage.getItem("agri-language") === "hi" ? "hi" : "en";
        } catch (error) {
            console.warn("Could not read the saved language preference.", error);
            return "en";
        }
    }

    function saveLanguage(language) {
        try {
            window.localStorage.setItem("agri-language", language);
        } catch (error) {
            console.warn("Could not save the language preference.", error);
        }
    }

    function normalize(value) {
        return value.replace(/\s+/g, " ").trim();
    }

    function translated(value, language) {
        const key = normalize(value);
        const dictionary = dictionaries[language] || {};
        return Object.prototype.hasOwnProperty.call(dictionary, key) ? dictionary[key] : key;
    }

    function isIgnored(element) {
        return !element ||
            element.isContentEditable ||
            Boolean(element.closest("script, style, noscript, textarea, code, pre, [data-no-translate]"));
    }

    function translateTextNode(node) {
        const element = node.parentElement;
        if (isIgnored(element)) {
            return;
        }

        if (!originalText.has(node)) {
            originalText.set(node, node.nodeValue);
        }

        const source = originalText.get(node);
        const key = normalize(source);
        const replacement = currentLanguage === "en"
            ? (dictionaries.en[key] || key)
            : (dictionaries.hi[key] || key);

        if (replacement === key) {
            node.nodeValue = source;
            return;
        }

        const leading = source.match(/^\s*/)[0];
        const trailing = source.match(/\s*$/)[0];
        node.nodeValue = leading + replacement + trailing;
    }

    function translateAttributes(element) {
        if (isIgnored(element)) {
            return;
        }

        const attributes = ["placeholder", "title", "aria-label", "alt"];
        if (element.matches('input[type="button"], input[type="submit"], input[type="reset"]')) {
            attributes.push("value");
        }

        let originals = originalAttributes.get(element);
        if (!originals) {
            originals = {};
            originalAttributes.set(element, originals);
        }

        attributes.forEach(function(name) {
            if (!element.hasAttribute(name)) {
                return;
            }
            if (!Object.prototype.hasOwnProperty.call(originals, name)) {
                originals[name] = element.getAttribute(name);
            }

            const source = originals[name];
            const key = normalize(source);
            const dictionary = dictionaries[currentLanguage] || {};
            const replacement = dictionary[key] || key;
            if (element.getAttribute(name) !== replacement) {
                element.setAttribute(name, replacement);
            }
        });
    }

    function scan(root) {
        if (!root || isIgnored(root.nodeType === Node.ELEMENT_NODE ? root : root.parentElement)) {
            return;
        }

        if (root.nodeType === Node.TEXT_NODE) {
            translateTextNode(root);
            return;
        }
        if (root.nodeType === Node.ELEMENT_NODE) {
            translateAttributes(root);
        }

        const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        let node;
        while ((node = walker.nextNode())) {
            translateTextNode(node);
        }

        if (root.querySelectorAll) {
            root.querySelectorAll("*").forEach(translateAttributes);
        }
    }

    function updateSwitcher() {
        document.querySelectorAll(".agri-language-option").forEach(function(button) {
            const selected = button.dataset.language === currentLanguage;
            button.setAttribute("aria-pressed", selected ? "true" : "false");
        });
    }

    function setLanguage(language) {
        currentLanguage = language === "hi" ? "hi" : "en";
        document.documentElement.lang = currentLanguage;
        const titleKey = normalize(originalTitle);
        document.title = (dictionaries[currentLanguage] || {})[titleKey] || originalTitle;
        saveLanguage(currentLanguage);
        scan(document.body);
        updateSwitcher();
    }

    function createSwitcher() {
        const navList = document.querySelector(
            "#navbar_global ul.navbar-nav.ml-auto, nav ul.navbar-nav.ml-auto, nav ul.navbar-nav.topnav"
        );
        const inNavigation = Boolean(navList);
        const switcher = document.createElement(inNavigation ? "li" : "div");
        switcher.className = inNavigation
            ? "nav-item agri-language-switcher agri-language-switcher--nav"
            : "agri-language-switcher agri-language-switcher--floating";
        switcher.setAttribute("data-no-translate", "");
        switcher.setAttribute("role", "group");
        switcher.setAttribute("aria-label", "Language");

        [
            { language: "en", label: "English" },
            { language: "hi", label: "हिंदी" }
        ].forEach(function(option, index) {
            if (index > 0) {
                const separator = document.createElement("span");
                separator.className = "agri-language-separator";
                separator.textContent = "|";
                switcher.appendChild(separator);
            }

            const button = document.createElement("button");
            button.type = "button";
            button.className = "agri-language-option";
            button.dataset.language = option.language;
            button.textContent = option.label;
            button.setAttribute("aria-pressed", "false");
            switcher.appendChild(button);
        });

        if (!inNavigation) {
            document.body.appendChild(switcher);
            return;
        }

        const logoutLink = Array.from(navList.querySelectorAll("a")).find(function(link) {
            return /logout/i.test(link.getAttribute("href") || "");
        });
        const logoutItem = logoutLink ? logoutLink.closest("li") : null;
        navList.insertBefore(switcher, logoutItem || null);
    }

    function loadDictionaries() {
        return Promise.all(["en", "hi"].map(function(language) {
            return fetch(new URL(language + ".json", languageDirectory), { cache: "no-cache" })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error("Could not load " + language + " translations.");
                    }
                    return response.json();
                });
        })).then(function(results) {
            dictionaries.en = results[0];
            dictionaries.hi = results[1];
        });
    }

    const stylesheet = document.createElement("link");
    stylesheet.rel = "stylesheet";
    stylesheet.href = new URL("../css/language-switcher.css", scriptUrl).href;
    document.head.appendChild(stylesheet);
    createSwitcher();

    document.addEventListener("click", function(event) {
        const button = event.target.closest(".agri-language-option");
        if (button) {
            setLanguage(button.dataset.language);
        }
    });

    const originalAlert = window.alert.bind(window);
    window.alert = function(message) {
        return originalAlert(translated(String(message), currentLanguage));
    };

    const originalConfirm = window.confirm.bind(window);
    window.confirm = function(message) {
        return originalConfirm(translated(String(message), currentLanguage));
    };

    loadDictionaries().then(function() {
        setLanguage(savedLanguage());
        observer = new MutationObserver(function(records) {
            records.forEach(function(record) {
                if (record.type === "childList") {
                    record.addedNodes.forEach(scan);
                } else if (record.type === "attributes") {
                    translateAttributes(record.target);
                }
            });
        });
        observer.observe(document.body, {
            attributes: true,
            attributeFilter: ["placeholder", "title", "aria-label", "alt"],
            childList: true,
            subtree: true
        });
    }).catch(function(error) {
        console.error("Language translations could not be loaded.", error);
    });
})();
