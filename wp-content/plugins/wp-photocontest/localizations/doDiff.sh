msgmerge -N --no-wrap wp-photocontest-nl_NL.pot wp-photocontest.pot -o wp-photocontest-nl_NL-draft.pot
diff --ignore-all-space --ignore-blank-lines wp-photocontest.pot wp-photocontest-nl_NL-draft.pot  | grep -v msgstr | grep -v .php | grep -v "\-\-\-"

msgmerge -N --no-wrap wp-photocontest-it_IT.pot wp-photocontest.pot -o wp-photocontest-it_IT-draft.pot
diff --ignore-all-space --ignore-blank-lines wp-photocontest.pot wp-photocontest-it_IT-draft.pot  | grep -v msgstr | grep -v .php | grep -v "\-\-\-"

msgmerge -N --no-wrap wp-photocontest-pl_PL.pot wp-photocontest.pot -o wp-photocontest-pl_PL-draft.pot
diff --ignore-all-space --ignore-blank-lines wp-photocontest.pot wp-photocontest-pl_PL-draft.pot  | grep -v msgstr | grep -v .php | grep -v "\-\-\-"

msgmerge -N --no-wrap wp-photocontest-si_SI.pot wp-photocontest.pot -o wp-photocontest-si_SI-draft.pot
diff --ignore-all-space --ignore-blank-lines wp-photocontest.pot wp-photocontest-si_SI-draft.pot  | grep -v msgstr | grep -v .php | grep -v "\-\-\-"

msgmerge -N --no-wrap wp-photocontest-bg_BG.pot wp-photocontest.pot -o wp-photocontest-bg_BG-draft.pot
diff --ignore-all-space --ignore-blank-lines wp-photocontest.pot wp-photocontest-bg_BG-draft.pot  | grep -v msgstr | grep -v .php | grep -v "\-\-\-"

msgmerge -N --no-wrap wp-photocontest-de_DE.pot wp-photocontest.pot -o wp-photocontest-de_DE-draft.pot
diff --ignore-all-space --ignore-blank-lines wp-photocontest.pot wp-photocontest-de_DE-draft.pot  | grep -v msgstr | grep -v .php | grep -v "\-\-\-"

msgmerge -N --no-wrap wp-photocontest-ru_RU.pot wp-photocontest.pot -o wp-photocontest-ru_RU-draft.pot
diff --ignore-all-space --ignore-blank-lines wp-photocontest.pot wp-photocontest-ru_RU-draft.pot  | grep -v msgstr | grep -v .php | grep -v "\-\-\-"
