# Language Files

This directory contains translation files for the URL Conflict Detector plugin.

## Files

- **url-conflict-detector.pot** - Template file containing all translatable strings
- **url-conflict-detector-de_DE.po** - German translation (source file)
- **url-conflict-detector-de_DE.mo** - German translation (compiled - needs to be generated)

## Compiling .mo Files

The .mo file is a binary compiled version of the .po file and needs to be generated using one of these methods:

### Method 1: Using Poedit (Recommended)
1. Download and install [Poedit](https://poedit.net/)
2. Open the .po file in Poedit
3. Save the file - Poedit will automatically generate the .mo file

### Method 2: Using msgfmt (Command Line)
```bash
msgfmt url-conflict-detector-de_DE.po -o url-conflict-detector-de_DE.mo
```

### Method 3: Online Tools
Use online .po to .mo converters like:
- https://po2mo.net/
- https://localise.biz/free/converter/po-to-mo

## Adding New Languages

To add a new language:
1. Copy the .pot file
2. Rename it to `url-conflict-detector-{locale}.po` (e.g., `url-conflict-detector-fr_FR.po` for French)
3. Translate all msgstr entries
4. Compile the .po file to .mo using one of the methods above
5. Place both files in this directory

## Language Codes

Common WordPress language codes:
- de_DE - German (Germany)
- fr_FR - French (France)
- es_ES - Spanish (Spain)
- it_IT - Italian
- nl_NL - Dutch
- pt_BR - Portuguese (Brazil)
- ru_RU - Russian
- ja - Japanese
- zh_CN - Chinese (Simplified)

For more information, visit: https://make.wordpress.org/polyglots/teams/
