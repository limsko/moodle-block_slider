<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Polskie teksty bloku slajdera.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['accentcolor'] = 'Kolor akcentu';
$string['accentcolor_help'] = 'Kolor strzałek, kropek paginacji i obramowania fokusu w formacie #rrggbb, np. #0f6cbf.';
$string['addslide'] = 'Dodaj slajd';
$string['alttext'] = 'Tekst alternatywny';
$string['alttext_help'] = 'Opisuje grafikę osobom korzystającym z czytników ekranu. Pozostaw puste, aby użyć tytułu slajdu. Grafika bez tekstu alternatywnego i bez tytułu jest traktowana jako dekoracyjna.';
$string['arrows'] = 'Strzałki';
$string['arrows_desc'] = 'Pokaż przyciski poprzedni i następny';
$string['aspectcustom'] = 'Własne proporcje';
$string['aspectheight'] = 'Wysokość';
$string['aspectratio'] = 'Proporcje grafiki';
$string['aspectratio_auto'] = 'Naturalny rozmiar grafiki';
$string['aspectratio_custom'] = 'Własne';
$string['aspectratio_help'] = 'Proporcje ramki slajdu (szerokość : wysokość). Wszystkie slajdy mają tę samą wysokość, więc strona nie przeskakuje przy ich zmianie. "Naturalny rozmiar grafiki" używa wysokości każdej grafiki.';
$string['aspectwidth'] = 'Szerokość';
$string['autoplay'] = 'Automatyczne odtwarzanie';
$string['autoplay_desc'] = 'Zmieniaj slajdy automatycznie';
$string['autoplay_help'] = 'Slajdy zmieniają się same. Odtwarzanie zatrzymuje się, gdy kursor jest nad slajderem albo fokus klawiatury jest w jego obrębie, i nie startuje u osób, które w ustawieniach systemu wybrały ograniczenie ruchu.';
$string['backtopage'] = 'Powrót na stronę';
$string['behaviour'] = 'Zachowanie';
$string['buttontext'] = 'Etykieta przycisku';
$string['buttontext_help'] = 'Gdy jest wpisana, a napisy są widoczne, link otwiera przycisk na slajdzie zamiast całego slajdu.';
$string['buttonx'] = 'Przycisk: {$a}';
$string['captionalign'] = 'Wyrównanie tekstu';
$string['captionalign_center'] = 'Do środka';
$string['captionalign_end'] = 'Do prawej';
$string['captionalign_start'] = 'Do lewej';
$string['captionbg'] = 'Kolor apli';
$string['captionbg_accent'] = 'Kolor akcentu, jasny tekst';
$string['captionbg_dark'] = 'Ciemna, jasny tekst';
$string['captionbg_help'] = 'Kolor cienia, ramki albo panelu pod napisem. Kolor tekstu jest dobierany tak, aby pozostał czytelny: jasny na ciemnej apli i kolorze akcentu, ciemny na jasnej.';
$string['captionbg_light'] = 'Jasna, ciemny tekst';
$string['captionhover'] = 'Napisy po najechaniu';
$string['captionhover_desc'] = 'Pokazuj napisy dopiero po najechaniu kursorem na slajd (na ekranach dotykowych są zawsze widoczne)';
$string['captionopacity'] = 'Krycie apli';
$string['captionopacity_help'] = 'Większe krycie poprawia czytelność tekstu, mniejsze pokazuje więcej grafiki.';
$string['captionposition'] = 'Położenie napisów';
$string['captionposition_bottom'] = 'Na dole';
$string['captionposition_center'] = 'Na środku';
$string['captionposition_top'] = 'Na górze';
$string['captions'] = 'Napisy';
$string['captionsize'] = 'Wielkość tekstu';
$string['captionsize_large'] = 'Duża';
$string['captionsize_medium'] = 'Średnia';
$string['captionsize_small'] = 'Mała';
$string['captionsize_xlarge'] = 'Bardzo duża';
$string['captionstyle'] = 'Układ napisów';
$string['captionstyle_below'] = 'Pod grafiką';
$string['captionstyle_box'] = 'Ramka na grafice';
$string['captionstyle_gradient'] = 'Cień na grafice';
$string['captionstyle_help'] = 'Gdzie i jak wyświetlany jest napis. "Obok grafiki" pokazuje slajd jako kartę z grafiką po lewej i panelem tekstu po prawej (na wąskich slajdach jeden pod drugim). Napisy pod grafiką i obok niej nigdy jej nie zasłaniają.';
$string['captionstyle_plain'] = 'Sam tekst na grafice (bez apli)';
$string['captionstyle_side'] = 'Obok grafiki';
$string['captionstyle_solid'] = 'Pełna apla na grafice';
$string['captionwidth'] = 'Szerokość napisów';
$string['captionwidth_help'] = 'Maksymalna szerokość tekstu napisu w stosunku do slajdu.';
$string['carousel'] = 'karuzela';
$string['choosepage'] = 'Wybierz slajd do wyświetlenia';
$string['configtitle'] = 'Tytuł bloku';
$string['configtitle_help'] = 'Wyświetlany jako nagłówek bloku. Pozostaw puste, aby ukryć nagłówek poza trybem edycji strony.';
$string['confirmdelete'] = 'Czy na pewno usunąć ten slajd? Jego grafiki również zostaną usunięte.';
$string['content'] = 'Treść';
$string['controls'] = 'Sterowanie';
$string['controlsposition'] = 'Położenie sterowania';
$string['controlsposition_bottom'] = 'Pasek pod slajderem';
$string['controlsposition_help'] = 'Pasek pod slajderem nigdy nie zasłania grafik ani napisów. Sterowanie na grafice oszczędza miejsce, a napisy zachowują wtedy odstęp od strzałek.';
$string['controlsposition_overlay'] = 'Na grafice';
$string['copylink'] = 'Kopiuj link';
$string['customaccent'] = 'Własny kolor';
$string['customaccent_desc'] = 'Użyj własnego koloru zamiast koloru motywu';
$string['deleteslide'] = 'Usuń slajd';
$string['description'] = 'Opis';
$string['device'] = 'Pokazuj na';
$string['device_all'] = 'Wszystkich urządzeniach';
$string['device_desktop'] = 'Tylko szerszych ekranach';
$string['device_help'] = 'Ukrywa blok na wąskich (poniżej 768 px) albo na szerszych ekranach. W trybie edycji strony blok jest zawsze widoczny.';
$string['device_mobile'] = 'Tylko wąskich ekranach';
$string['dots'] = 'Paginacja';
$string['dots_desc'] = 'Pokaż kropki paginacji pod slajderem';
$string['editslide'] = 'Edytuj slajd';
$string['effect'] = 'Przejście';
$string['effect_fade'] = 'Przenikanie';
$string['effect_help'] = 'Przesuwanie przewija slajdy w poziomie i pozwala pokazać kilka slajdów naraz. Przenikanie pokazuje jeden slajd.';
$string['effect_slide'] = 'Przesuwanie';
$string['error_color'] = 'Podaj kolor w formacie #rrggbb.';
$string['error_interval'] = 'Wartość musi wynosić co najmniej {$a} ms.';
$string['error_link'] = 'Podaj pełny adres zaczynający się od http:// lub https:// albo ścieżkę w tym serwisie zaczynającą się od /.';
$string['error_positive'] = 'Podaj liczbę dodatnią.';
$string['filtercode'] = 'Aby wyświetlić ten slajder w dowolnym tekście, użyj kodu:';
$string['frameless'] = 'Bez ramki';
$string['frameless_desc'] = 'Usuń obramowanie, tło i marginesy wewnętrzne bloku';
$string['gap'] = 'Odstęp między slajdami';
$string['gotoslide'] = 'Przejdź do slajdu {$a}';
$string['image'] = 'Grafika';
$string['image_help'] = 'Główna grafika slajdu. Dla najlepszej jakości użyj grafiki co najmniej tak szerokiej jak slajder i o proporcjach ustawionych w ustawieniach bloku.';
$string['imagefit'] = 'Dopasowanie grafiki';
$string['imagefit_contain'] = 'Pokaż całą grafikę';
$string['imagefit_cover'] = 'Wypełnij ramkę (przytnij krawędzie)';
$string['imagefit_help'] = 'Sposób wyświetlania grafik o innych proporcjach niż ramka.';
$string['interval'] = 'Czas wyświetlania slajdu (ms)';
$string['interval_help'] = 'Jak długo slajd jest widoczny przed przejściem do następnego, w milisekundach (1000 ms = 1 s).';
$string['layout'] = 'Układ';
$string['link'] = 'Link';
$string['link_help'] = 'Pełny adres (https://...) albo ścieżka w tym serwisie, np. /course/view.php?id=2.';
$string['linkcopied'] = 'Link skopiowany do schowka';
$string['loop'] = 'Zapętlenie';
$string['loop_desc'] = 'Po ostatnim slajdzie wracaj do pierwszego';
$string['manageslides'] = 'Zarządzaj slajdami';
$string['maxwidth'] = 'Maksymalna szerokość (px)';
$string['maxwidth_help'] = 'Ogranicza szerokość slajdera. 0 oznacza pełną szerokość obszaru.';
$string['mobileaspectratio'] = 'Proporcje na wąskich ekranach';
$string['mobileaspectratio_help'] = 'Proporcje używane poniżej 768 px, np. wyższa ramka dla grafik przygotowanych na telefony.';
$string['mobileaspectratio_same'] = 'Jak na szerszych ekranach';
$string['mobileimage'] = 'Grafika na wąskie ekrany';
$string['mobileimage_help'] = 'Opcjonalna grafika wyświetlana poniżej 768 px zamiast głównej, np. z większym tekstem albo w innym kadrze.';
$string['newwindow'] = 'Nowe okno';
$string['newwindow_desc'] = 'Otwieraj link w nowym oknie';
$string['nextslide'] = 'Następny slajd';
$string['noimage'] = 'Brak grafiki';
$string['noslides'] = 'Nie ma jeszcze slajdów. Dodaj pierwszy slajd.';
$string['notitle'] = 'Bez tytułu';
$string['openlink'] = 'Otwórz link';
$string['pause'] = 'Wstrzymaj pokaz slajdów';
$string['pausebutton'] = 'Przycisk pauzy';
$string['pausebutton_desc'] = 'Pokaż przycisk wstrzymania i wznowienia odtwarzania';
$string['pausebutton_help'] = 'Widoczny przycisk pauzy jest wymagany dla treści zmieniających się samoczynnie według wytycznych dostępności (WCAG 2.2.2).';
$string['pauseonhover'] = 'Pauza po najechaniu';
$string['pauseonhover_desc'] = 'Wstrzymuj odtwarzanie, gdy kursor jest nad slajderem';
$string['play'] = 'Odtwórz pokaz slajdów';
$string['pluginname'] = 'Slajder';
$string['previousslide'] = 'Poprzedni slajd';
$string['privacy:metadata:block'] = 'Blok slajdera przechowuje ustawienia w podsystemie bloków.';
$string['privacy:metadata:block_slider_slides'] = 'Slajdy bloku slajdera. Slajdery dodane do kokpitu użytkownika należą do tego użytkownika.';
$string['privacy:metadata:block_slider_slides:alttext'] = 'Tekst alternatywny grafiki slajdu.';
$string['privacy:metadata:block_slider_slides:buttontext'] = 'Etykieta przycisku slajdu.';
$string['privacy:metadata:block_slider_slides:description'] = 'Opis slajdu.';
$string['privacy:metadata:block_slider_slides:link'] = 'Link slajdu.';
$string['privacy:metadata:block_slider_slides:subtitle'] = 'Podtytuł slajdu.';
$string['privacy:metadata:block_slider_slides:timemodified'] = 'Czas ostatniej zmiany slajdu.';
$string['privacy:metadata:block_slider_slides:title'] = 'Tytuł slajdu.';
$string['privacy:metadata:core_files'] = 'Grafiki slajdów są zapisywane w systemie plików.';
$string['reorderhint'] = 'Przeciągnij slajdy, aby zmienić ich kolejność.';
$string['rounded'] = 'Zaokrąglenie narożników';
$string['showcaptions'] = 'Pokaż napisy';
$string['showcaptions_desc'] = 'Pokaż tytuł, podtytuł, opis i przycisk slajdu';
$string['showcaptions_help'] = 'Napisy na grafice dostają ciemny cień albo ramkę, aby tekst był czytelny na każdej grafice.';
$string['sizepx'] = '{$a} px';
$string['slide'] = 'slajd';
$string['slidedeleted'] = 'Slajd został usunięty.';
$string['slidenumber'] = 'Slajd {$a}';
$string['slider:addinstance'] = 'Dodaj nowy blok slajdera';
$string['slider:manage'] = 'Zarządzaj slajdami bloku slajdera';
$string['slider:myaddinstance'] = 'Dodaj nowy blok slajdera do kokpitu';
$string['sliderlabel'] = 'Slajder';
$string['slides'] = 'Slajdy';
$string['slidesaved'] = 'Slajd został zapisany.';
$string['slidestoshow'] = 'Liczba widocznych slajdów';
$string['slidestoshow_help'] = 'Liczba slajdów widocznych obok siebie. W wąskich obszarach jest zmniejszana automatycznie, tak aby każdy slajd miał co najmniej ok. 220 px szerokości.';
$string['slidetitle'] = 'Tytuł';
$string['slidexofy'] = '{$a->index} z {$a->total}';
$string['speed'] = 'Szybkość przejścia';
$string['speed_help'] = 'Czas trwania animacji przejścia między slajdami. Osoby preferujące ograniczenie ruchu zawsze widzą zmianę bez animacji.';
$string['speed_none'] = 'Bez animacji';
$string['speedms'] = '{$a} ms';
$string['subtitle'] = 'Podtytuł';
$string['swipe'] = 'Przesuwanie gestem';
$string['swipe_desc'] = 'Zmieniaj slajdy gestem na ekranach dotykowych albo przeciągnięciem myszą';
$string['visibility'] = 'Widoczność';
$string['visible_desc'] = 'Pokazuj ten slajd w slajderze';
