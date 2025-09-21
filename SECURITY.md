# 🔒 Security Policy

## Unterstützte Versionen

Wir bieten Sicherheitsupdates für die folgenden Plugin-Versionen an:

| Version | Unterstützt          |
| ------- | -------------------- |
| 1.8.x   | ✅ Vollständig       |
| 1.7.x   | ✅ Kritische Fixes   |
| 1.6.x   | ⚠️ Nur kritische     |
| < 1.6   | ❌ Nicht unterstützt |

## Sicherheitslücke melden

### 🚨 Responsible Disclosure

Wenn Sie eine Sicherheitslücke im KaPlan Gottesdienste Plugin gefunden haben, melden Sie diese bitte **privat** und **nicht** über öffentliche Issues.

### 📧 Kontakt für Sicherheitsmeldungen

**Email**: Über [jlsoftware.de](http://www.jlsoftware.de) Kontakt aufnehmen  
**Antwortzeit**: Normalerweise innerhalb von 48 Stunden  
**Sprachen**: Deutsch, Englisch  

### 📋 Was sollten Sie in Ihrem Bericht angeben?

1. **Beschreibung der Sicherheitslücke**
   - Art des Problems (XSS, SQL Injection, etc.)
   - Betroffene Plugin-Version(en)
   - Schweregrad der Lücke

2. **Schritte zur Reproduktion**
   - Detaillierte Anweisungen
   - Proof-of-Concept (falls möglich)
   - Screenshots oder Videos

3. **Potenzielle Auswirkungen**
   - Welche Daten könnten betroffen sein?
   - Welche Benutzer sind gefährdet?

4. **Ihre Umgebung**
   - WordPress-Version
   - PHP-Version
   - Plugin-Version
   - Browser (falls relevant)

### 🛡️ Was wir garantieren

- **Vertraulichkeit**: Ihre Meldung wird vertraulich behandelt
- **Schnelle Reaktion**: Antwort innerhalb von 48 Stunden
- **Anerkennung**: Sie werden (mit Ihrer Erlaubnis) in den Release Notes erwähnt
- **Kein rechtliches Vorgehen**: Sofern Sie verantwortungsvoll handeln

## 🔍 Sicherheitspraktiken

### Was wir tun

- ✅ **Input-Validierung**: Alle Benutzereingaben werden validiert
- ✅ **Output-Escaping**: HTML-Ausgaben werden escaped (`esc_html`, `esc_url`)
- ✅ **Sichere API-Aufrufe**: HTTPS und Zertifikatsvalidierung
- ✅ **Code-Reviews**: Alle Änderungen werden überprüft

### Bekannte Sicherheitsüberlegungen

1. **API-Schlüssel**: Das Plugin überträgt KaPlan-Zugangsdaten über HTTPS
2. **XSS-Schutz**: Alle dynamischen Inhalte werden escaped
3. **Input-Sanitization**: Parameter werden validiert und bereinigt

## 🚀 Sicherheitsupdates

### Automatische Updates

Sicherheitsupdates werden priorisiert und über das automatische Update-System verteilt:

1. **Kritische Sicherheitslücken**: Sofortige Patches
2. **Mittlere Sicherheitsprobleme**: Updates innerhalb von 7 Tagen
3. **Niedrige Sicherheitsverbesserungen**: Reguläre Updates

### Update-Benachrichtigungen

- WordPress Admin zeigt verfügbare Updates an
- Sicherheitsupdates werden als "wichtig" markiert
- Release Notes enthalten Sicherheitsinformationen

## 📊 Schweregrad-Einstufung

### 🔴 Kritisch
- Remote Code Execution
- SQL Injection
- Authentifizierungs-Umgehung
- Privileg-Eskalation

### 🟠 Hoch
- Cross-Site Scripting (XSS)
- Cross-Site Request Forgery (CSRF)
- Informationsleck sensitiver Daten

### 🟡 Mittel
- Informationsleck nicht-sensitiver Daten
- Denial of Service (DoS)
- Bypass von Sicherheitsmaßnahmen

### 🟢 Niedrig
- Informationsleck öffentlicher Daten
- Minimale Sicherheitsverbesserungen

## 🛠️ Für Entwickler

### Sicherheits-Checklist

Bevor Sie Code beitragen:

- [ ] Alle Eingaben validiert?
- [ ] Alle Ausgaben escaped?
- [ ] CSRF-Schutz implementiert?
- [ ] Berechtigung geprüft?
- [ ] Sichere API-Aufrufe?
- [ ] Keine Geheimnisse im Code?
- [ ] Security-Tests durchgeführt?

---

## 🙏 Danksagung

Wir danken der Security-Community für ihre Hilfe beim Schutz unserer Benutzer.

---

*Letzte Aktualisierung: September 2025*
