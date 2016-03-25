{*
Dieses Template ist in Smarty geschrieben. http://www.smarty.net/

ACHTUNG: Der Name der Template-Datei sollte exakt dem Template-Namen der in der Boostrap.php definierten
Komponente entsprechen. Siehe Bootstrap::TEMPLATE

Shopware stellt uns automatisch ein Array $Data zur Verfügung, die sämtliche Werte unserer definierten
Komponentenfelder beinhalten.
Alle Werte kann man sich ausgeben lassen mit {$Data|print_r}
*}
<div class="instagram-element panel has--border">
    <ul>
        {foreach from=$Data.image_urls item=image_url}
            <li>
                <img src="{$image_url}" alt="Instagram Bild" />
            </li>
        {/foreach}
    </ul>
</div>
