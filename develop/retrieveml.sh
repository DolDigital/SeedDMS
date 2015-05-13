#!/bin/sh
# This command retrieves the strings that need to be translated
sgrep -o "%r\n" '"getMLText(\"" __ "\""' */*.php views/bootstrap/*.php |sort|uniq -c
