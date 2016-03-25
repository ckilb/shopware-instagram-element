<?php
use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Models\Emotion\Library\Component;

require_once(__DIR__ . '/Services/InstagramService.php');

/**
 * Class Shopware_Plugins_Backend_InstagramEmotionElement_Bootstrap
 *
 * Der Klassenname der Bootstrap-Klasse besteht aus:
 *
 * - "Shopware_Plugins_"
 *
 * - Typ des Plugins ("Frontend" oder "Backend").
 *
 * ACHTUNG: Hier bitte darauf achten, dass das Plugin entsprechend auch
 * im richtigen Verzeichnis liegt ("Local/Frontend" bzw. "Local/Backend"). Ein Einkaufswelt-Element ist immer vom
 * Typ "Backend", auch wenn es zum Teil natürlich auch Code zur Ausgabe im Frontend beinhaltet.
 *
 * - Verzeichnis, in dem das Plugin liegt (in diesem Fall "CkdotInstagramEmotionElement").
 *
 * ACHTUNG: Auch hier auf das richtige Verzeichnis achten. Dieses Plugin muss beispielsweise im Pfad
 * engine/Shopware/Plugins/Local/Backend/CkdotnstagramEmotionElement liegen.
 *
 * - "_Bootstrap"
 *
 * TIPP: Es bietet sich eventuell an, das Plugin in einem eigenen Repository-Verzeichnis zu entwickeln und im Pfad
 * engine/Shopware/Plugins/Local/Backend/ einen Symlink mit dem Linux-Befehl
 * ln -s {pfad-zum-repo} InstagramEmotionElement
 * zu erstellen.
 *
 * @author Christian Kilb
 */
class Shopware_Plugins_Backend_CkdotInstagramEmotionElement_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Der Name des Templates gibt an, in welchem Verzeichnis unsere Template-Dateien liegen.
     * Da wir diesen Namen an mehreren Stellen benötigen, lagern wir ihn in eine Konstante aus.
     * Der Template-Name sollte immter in snake_case geschrieben sein: Alle Buchstaben klein und mit Unterstrichen
     * (keine Minuszeichen) getrennt.
     * Dieser Template-Name sollte einmalig sein, sodass es mit großer Wahrscheinlichkeit kein 2. Plugin geben wird,
     * welches den selben Namen verwendet.
     */
    const TEMPLATE = 'instagram_element';

    /**
     * Dies ist der Name der CSS-Klasse für das Einkaufswelt-Element.
     * Dieser Name sollte den Konventionen von CSS-Klassen entsprechen: Alle Buchstaben klein geschrieben und mit
     * Minuszeichen (keine Unterstriche) getrennt.
     */
    const CLS = 'instagram-element';

    /**
     * Gibt den XType für das Dropdown-Element zum setzen einer maximalen Bildanzahl fest.
     * Alle Buchstaben sollten klein geschrieben werden und mit Minuszeichen (keine Unterstriche) getrennt.
     *
     * ACHTUNG: Dieser XType sollte - ohne dem "widget."-Prefix exakt dem Alias für das Element
     * in unserer JavaScript-Datei entsprechen.
     */
    const LIMIT_XTYPE = 'instagram-element-limit';

    /**
     * Dieser Service beinhaltet die Logik zum Abfragen von Bildern von Instagram
     * @var InstagramService
     */
    private $instagram;

    /**
     * Wir überschreiben den Konstruktor, um direkt beim Instanziieren der Bootstrap-Klasse unsere Dependencies
     * festzulegen. Im Moment haben wir nur eine Dependency: Den InstagramService zur Abfrage von Bildern von Instagram.
     *
     * @param string $name
     * @param Enlight_Config|null $info
     */
    public function __construct($name, $info = null)
    {
        $cache           = Shopware()->Cache();
        $this->instagram = new InstagramService($cache);

        return parent::__construct($name, $info);
    }

    /**
     * Diese Methode wird von Shopware erwartet und gibt an, welche Aktionen für das Plugin zur Verfügung stehen.
     * Wir möchten natürlich ermöglichen, dass das Plugin installiert und aktiviert werden kann, also geben wir
     * install und enable = true zurück.
     *
     * @return bool[]
     */
    public function getCapabilities()
    {
        return [
            'install' => true,
            'enable'  => true,
        ];
    }

    /**
     * Diese Methode wird von Shopware erwartet und gibt den Namen des Plugins zurück.
     * Das heißt, mit diesem Namen wird das Plugin im Plugin-Manager aufgelistet.
     *
     * ACHTUNG: Wenn Du den Namen des Plugins zwischenzeitlich änderst, hat das wahrscheinlich keine Auswirkungen
     * auf die Anzeige im Plugin-Manager. Hierbei hilft es oft, das Plugin neu zu installieren.
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Instagram Einkaufswelten-Element';
    }

    /**
     * Diese Methode wird von Shopware erwartet und gibt die aktuelle Version des Plugins zurück.
     * Für den Anfang sollte diese Version immer 1.0.0 sein.
     * Später, wenn Du Änderungen am Plugin machen möchtest, aber verhindern möchtest,
     * dass der Benutzer das Plugin neu installieren muss (was zur Folge hätte, dass alle Einkaufswelt-Elemente dieses
     * Plugins auf den Einkaufswelten gelöscht werden), kannst Du hier die Version erhöhen und eine update()-Methode
     * implementieren..
     *
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * Diese Methode wird von Shopware erwartet und gibt weitere Informationen über dieses Plugin zurück.
     * Shopware erwartet auch hier wieder die Version und das Label. Um Code-Redundanz zu vermeiden, sollten
     * wir uns für diese beiden Optionen unseren bereits geschriebenen Methoden bedienen.
     *
     * @return string[]
     */
    public function getInfo()
    {
        return [
            'version'     => $this->getVersion(),
            'label'       => $this->getLabel(),
            'supplier'    => 'Cilb.de',
            'author'      => 'Christian Kilb',
            'description' => 'Anzeige von Instagram-Bildern in Einkaufswelten',
            'link'        => 'http://www.cilb.de'
        ];
    }

    /**
     * Diese Methode wird von Shopware aufgerufen, wenn der Benutzer im Backend das Plugin installieren möchte.
     * Die Methode sollte true zurückgeben, wenn die Installation erfolgreich war.
     * Im Grunde genommen können sämtliche Aktionen, die für die Installation notwendig sind, hier implementiert werden.
     * Sauberer und übersichtlicher ist es aber, wenn die einzelnen Aktionen in privaten Methoden mit treffendem
     * Namen zusammengefasst werden und diese dann von der install() Methode aufgerufen werden.
     *
     * @return bool
     */
    public function install()
    {
        $component = $this->createInstagramComponent();
        $this->createInstagramComponentFields($component);
        $this->registerEvents();

        return true;
    }

    /**
     * Diese Methode wird von Shopware aufgerufen, wenn der Benutzer im Backend das Plugin aktivieren möchte.
     * Hier legen wir fest, dass der Template- und Theme-Cache gelöscht werden soll, sodass die Style-Datei vom
     * Plugin mitkompiliert wird.
     * @return array
     */
    public function enable()
    {
        return [
            'success'         => true, // aktivieren des Plugins ist immer erfolgreich
            'invalidateCache' => ['template', 'theme'] // Cache invalidieren
        ];
    }

    /**
     * Diese Methode definiert ein neues Einkaufswelt-Element, welches später per Drag & Drop beim Bearbeiten
     * einer Einkaufswelt hinzugefügt werden kann.
     *
     * @return Component
     */
    private function createInstagramComponent()
    {

        return $this->createEmotionComponent(array(
            'name'        => 'Instagram Element', // so lautet der Name des Elements beim Bearbeiten der Einkaufswelt
            'cls'         => self::CLS, // siehe Beschreibung zur Konstante
            'template'    => self::TEMPLATE, // siehe Beschreibung zur Konstante
            'description' => 'Einbinden von Fotos aus Instagram', // kurze Beschreibung des Elements
        ));
    }

    /**
     * Damit der Benutzer ein Einkaufswelt-Element hinzufügen kann, muss er es vorher konfigurieren.
     * Dazu müssen wir entsprechend Felder anlegen, die er in einem Formular ausfüllen kann.
     * Diese Methode fügt diese Felder der Methode hinzu.
     *
     * @param Component $component
     */
    private function createInstagramComponentFields(Component $component)
    {
        $component->createTextField([
            'name'       => 'username', // eindeutiger Name: so wird die Template-Variable für diesen Wert später heißen
            'fieldLabel' => 'Instagram-Nutzername', // wird im Backend im Formular neben dem Textfeld angezeigt
            'allowBlank' => false // wenn true, ist dieses Feld optional. Wenn false, muss es ausgefüllt werden
        ]);

        /*
         * Hier fügen wir ein generisches Feld hinzu, welches wir in unserer JavaScript-Datei an Hand des
         * X-Types genauer definieren werden:
         * Im Backend soll für dieses Feld ein Dropdown zur Festlegung einer Maximalanzahl an Bildern erscheinen.
         * Hierfür würde zwar ein normales TextField ausreichen; dieses Plugin soll aber demonstrieren, wie
         * man Checkboxes mit eigenen Werten anlegt.
         */
        $component->createField([
            'name'       => 'limit',

            // Wichtig! Der X-Type muss klein geschrieben sein und mit Minus
            'xtype'      => self::LIMIT_XTYPE,
            'fieldLabel' => 'Anzahl Bilder',
            'allowBlank' => false
        ]);
    }

    /**
     * Hier registrieren wir:
     * 1. eine Callback-Funktion für das Shopware-Event Emotion:AddElement.
     * Anders als der Name des Events eventuell vermuten lässt, wird dieses Element NICHT beim Hinzufügen
     * eines neuen Elements in eine Einkaufswelt im Backend ausgeführt, sondern wenn ein Einkaufswelt-Element
     * im Frontend wird.
     * Das heißt: Wenn der Benutzer unsere Instagram-Komponente einer Einkaufswelt hinzugefügt hat, und diese
     * Einkaufswelt darauf im Shop geladen wird, dann wird die onLoadElement Methode in dieser Datei ausgeführt.
     * 2: eine Callback-Funktion für das Shopware-Event zum Kompilieren von Less-Dateien zu CSS.
     * Das heißt: Wenn im Backend das Theme neu kompiliert wird, wird die onCollectLessFiles Methode aufgerufen.
     */
    private function registerEvents()
    {
        $this->subscribeEvent(
            'Shopware_Controllers_Widgets_Emotion_AddElement',
            'onLoadElement'
        );

        $this->subscribeEvent(
            'Theme_Compiler_Collect_Plugin_Less',
            'onCollectLessFiles'
        );
    }

    /**
     * Diese Methode wird jedes Mal aufgerufen, wenn unsere Instagram-Komponente im Frontend geladen wird.
     * Normalerweise hätten wir in unserem Frontend-Template (instagram_element.tpl) nur die zwei Werte für
     * den Instagram-Usernamen sowie das Limit (maximale Anzahl an Bildern) zur Verfügung.
     * Das wichtigste fehlt uns aber noch: die eigentlichen Bilder des Accounts.
     * Diese müssen vorher abgefragt werden, was wir hiermit machen.
     *
     * @param Enlight_Event_EventArgs $args
     * @return array
     */
    public function onLoadElement(Enlight_Event_EventArgs $args)
    {
        /**
         * $data enthält die Werte, die auch im Template als $Data Array zu Verfügung stehen würden:
         * username und limit
         * Dieses Array werden wir um die Bild-URLs erweitern und dann in dieser Funktion zurückgeben.
         * @var string[] $data
         */
        $data    = $args->getReturn();

        /**
         * $element beinhaltet Daten zum dem aktuellen Einkaufswelt-Element, für das die Funktion onLoadElement
         * aufgerufen wurde.
         * @var array $element
         */
        $element = $args->get('element');

        /**
         * An Hand des Template-Names des Elements überprüfen wir, ob aktuell die Instagram-Komponente geladen wird.
         * Wenn nicht, brechen wir ab, indem wir die aktuellen Daten unverändert zurückgeben.
         */
        if (self::TEMPLATE !== $element['component']['template']) {
            return $data;
        }

        /**
         * Jetzt holen wir uns lediglich noch die Bilder uns fügen die unseren Daten hinzu.
         */
        $data['image_urls'] = $this->instagram->getImageUrls($data['username'], $data['limit']);

        return $data;
    }

    /**
     * Diese Methode wird aufgerufen, wenn der Theme-Cache inklusive der Less-Dateien neu kompiliert wird.
     * Less ist eine Style-Sprache, die auf CSS aufsetzt und auch von Shopware automatisch zu CSS umgewandelt wird.
     * Diese Funktion fügt die Less-Datei dieses Plugins zu den Shopware-Less-Dateien hinzu.
     * @return ArrayCollection
     */
    public function onCollectLessFiles()
    {
        $lessDir = __DIR__ . '/Views/frontend/_public/src/less/';

        $less = new \Shopware\Components\Theme\LessDefinition(
            [],
            [
                $lessDir . 'instagram-element.less'
            ]
        );

        return new ArrayCollection(array($less));
    }
}