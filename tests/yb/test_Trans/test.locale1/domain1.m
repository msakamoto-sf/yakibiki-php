; starting with ";" means comment line.
; between '====' and '----' means source text
; between '----' and '====' (or EOF) means translated text
; heading and trailing new line is removed. (trim() is called)
;
; All trailing new line is converted CRLF internal.
====
; this line is ignored as comment line.

ABC

; this line is ignored as comment line.
----
; this line is ignored as comment line.

Translated ABC

; this line is ignored as comment line.
====

DEF %key1 %key2

----

Translated %key1 , %key2


====
GHI
; inside new line is NOT ignored.

JKL
----
Translated GHI.

Translated JKL.
====
MNO
----
; This translated string is empty.
