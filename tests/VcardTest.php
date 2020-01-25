<?php

declare(strict_types=1);
/**
 * Tests for the Vcard class
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2019-05-28
 * @since   2017-12-12
 */

namespace Tests;

use Contacts\Vcard;
use Contacts\ContactsException;
use PHPUnit\Framework\TestCase;

class VcardTest extends TestCase
{
    public $branch = 'master';

    public function testDebugReturnsString(): void
    {
        $vcard = new Vcard();

        $expectedResult = "<pre>**PROPERTIES**\n1\n\n**DEFINED ELEMENTS**\n1";
        $result         = $vcard->debug();

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetPropertiesReturnsAllProperties(): void
    {
        $vcard = new Vcard();
        $vcard->addFullName('Jane Doe');

        $expectedResult = 'FN:Jane Doe';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetDefinedElementsReturnsAllProperties(): void
    {
        $vcard = new Vcard();
        $vcard->addFullName('Jane Doe');

        $expectedResult = '1';
        $result         = $vcard->getDefinedElements()['FN'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddFullName(): void
    {
        $vcard = new Vcard();
        $vcard->addFullName('Jane Doe');

        $expectedResult = 'FN:Jane Doe';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddName(): void
    {
        $vcard = new Vcard();
        $vcard->addName('Doe', 'Jane', 'Mary, Elizabeth', 'Mrs., Dr.', 'PhD, MD');

        $expectedResult = 'N:Doe;Jane;Mary,Elizabeth;Mrs.,Dr.;PhD,MD';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddNickname(): void
    {
        $vcard = new Vcard();
        $vcard->addNicknames(['Jan', 'Janet']);

        $expectedResult = 'NICKNAME:Jan,Janet';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddPhotoUrl(): void
    {
        $vcard = new Vcard();
        $vcard->addPhoto('https://raw.githubusercontent.com/jaredhowland/contacts/'.$this->branch.'/tests/files/photo.jpg');

        $expectedResult = file_get_contents('tests/files/expectedPhoto.txt');
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddPhotoBinary(): void
    {
        $vcard = new Vcard();
        $vcard->addPhoto(file_get_contents('tests/files/photoBinary.txt'), false);

        $expectedResult = file_get_contents('tests/files/expectedPhoto.txt');
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddBirthdayWithYear(): void
    {
        $vcard = new Vcard();
        $vcard->addBirthday(10, 5);

        $expectedResult = 'BDAY;X-APPLE-OMIT-YEAR=1604:1604-10-05';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddBirthdayWithoutYear(): void
    {
        $vcard = new Vcard();
        $vcard->addBirthday(10, 5, 1980);

        $expectedResult = 'BDAY:1980-10-05';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddAddress(): void
    {
        $vcard = new Vcard();
        $vcard->addAddress('P.O. Box 1105', 'Big Corporation', '1540 Main St.', 'Provo', 'UT', '84602', 'USA',
            ['postal', 'parcel', 'home']);

        $expectedResult = 'ADR;TYPE=postal,parcel,home:P.O. Box 1105;Big Corporation;1540 Main St.;Provo;UT;84602;USA';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddLabel(): void
    {
        $vcard = new Vcard();
        $vcard->addLabel('Big Corporation\n1105 Main St.\nProvo, UT 84602\nU.S.A.', ['home', 'postal', 'parcel']);

        $expectedResult = 'LABEL;TYPE=home,postal,parcel:Big Corporation\n1105 Main St.\nProvo\, UT 84602\nU.S.A.';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddTelephoneWithAreaCode(): void
    {
        $vcard = new Vcard();
        $vcard->addTelephone('709.567-9087', ['cell', 'iphone', 'pref']);

        $expectedResult = 'TEL;TYPE=cell,iphone,pref:(709) 567-9087';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddTelephoneWithoutAreaCode(): void
    {
        $vcard = new Vcard();
        $vcard->addTelephone('567-9087', ['cell', 'pref']);

        $expectedResult = 'TEL;TYPE=cell,pref:(801) 567-9087';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddTelephoneInvalid(): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addTelephone('709.567-90871', ['cell', 'iphone', 'pref']);

        $expectedResult = null;
        $result         = $vcard->getProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddEmailValid(): void
    {
        $vcard = new Vcard();
        $vcard->addEmail('test@test.com', ['internet', 'pref']);

        $expectedResult = 'EMAIL;TYPE=internet,pref:test@test.com';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddEmailInvalid(): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addEmail('test.com', ['internet', 'pref']);

        $expectedResult = null;
        $result         = $vcard->getProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddMailer(): void
    {
        $vcard = new Vcard();
        $vcard->addMailer('Outlook');

        $expectedResult = 'MAILER:Outlook';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $originalTimeZone Time zone as passed to `addTimeZone` method
     * @param string $expectedResult   Expected value
     *
     * @dataProvider providerTestAddTimeZoneValid
     *
     * @throws ContactsException
     */
    public function testAddTimeZoneValid($originalTimeZone, $expectedResult): void
    {
        $vcard = new Vcard();
        $vcard->addTimeZone($originalTimeZone);

        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestAddTimeZoneValid(): array
    {
        return [
            ['-7', 'TZ:-07:00'],
            ['7:30', 'TZ:+07:30'],
            ['+7:30', 'TZ:+07:30'],
            ['-07', 'TZ:-07:00'],
            ['-7:00', 'TZ:-07:00'],
            ['-07:30', 'TZ:-07:30'],
            ['-14', 'TZ:-14:00'],
        ];
    }

    /**
     * @param string $originalTimeZone Time zone as passed to `addTimeZone` method
     * @param string $expectedResult   Expected value
     *
     * @dataProvider providerTestAddTimeZoneInvalid
     *
     * @throws ContactsException
     */
    public function testAddTimeZoneInvalid($originalTimeZone, $expectedResult): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addTimeZone($originalTimeZone);

        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestAddTimeZoneInvalid(): array
    {
        return [
            ['', null],
            ['-15', null],
            ['13:30', null],
            ['-00:00', null],
            ['monkey', null],
        ];
    }

    /**
     * @param float  $originalLat    Latitude coordinates as passed to `addLatLong` method
     * @param float  $originalLong   Longitude coordinates as passed to `addLatLong` method
     * @param string $expectedResult Expected value
     *
     * @throws ContactsException
     * @dataProvider providerTestAddLatLongValid
     */
    public function testAddLatLongValid(float $originalLat, float $originalLong, $expectedResult): void
    {
        $vcard = new Vcard();
        $vcard->addLatLong($originalLat, $originalLong);

        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestAddLatLongValid(): array
    {
        return [
            [0, 0, 'GEO:0.000000;0.000000'],
            [-89.123456, 179.654321, 'GEO:-89.123456;179.654321'],
            [-90, -180, 'GEO:-90.000000;-180.000000'],
            [90, 180, 'GEO:90.000000;180.000000'],
        ];
    }

    /**
     * @param float  $originalLat    Latitude coordinates as passed to `addLatLong` method
     * @param float  $originalLong   Longitude coordinates as passed to `addLatLong` method
     * @param string $expectedResult Expected value
     *
     * @dataProvider providerTestAddLatLongInvalid
     *
     * @throws ContactsException
     */
    public function testAddLatLongInvalid($originalLat, $originalLong, $expectedResult): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addLatLong($originalLat, $originalLong);

        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestAddLatLongInvalid(): array
    {
        return [
            [-90.123456, 180.654321, null],
            [-90, 181, null],
            [-91, 180, null],
        ];
    }

    public function testAddTitle(): void
    {
        $vcard = new Vcard();
        $vcard->addTitle('CEO');

        $expectedResult = 'TITLE:CEO';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddRole(): void
    {
        $vcard = new Vcard();
        $vcard->addRole('CEO');

        $expectedResult = 'ROLE:CEO';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddLogoUrl(): void
    {
        $vcard = new Vcard();
        $vcard->addLogo('https://raw.githubusercontent.com/jaredhowland/contacts/'.$this->branch.'/tests/files/photo.jpg');

        $expectedResult = file_get_contents('tests/files/expectedPhoto.txt');
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddLogoBinary(): void
    {
        $vcard = new Vcard();
        $vcard->addLogo(file_get_contents('tests/files/photoBinary.txt'), false);

        $expectedResult = file_get_contents('tests/files/expectedPhoto.txt');
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddAgent(): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addAgent('Test');

        $expectedResult = null;
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddOrganizations(): void
    {
        $vcard = new Vcard();
        $vcard->addOrganizations(['Big Organization 1', 'Big Organization 2']);

        $expectedResult = 'ORG:Big Organization 1;Big Organization 2';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddCategories(): void
    {
        $vcard = new Vcard();
        $vcard->addCategories(['Home', 'Work']);

        $expectedResult = 'CATEGORIES:Home,Work';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddNote(): void
    {
        $vcard = new Vcard();
        $vcard->addNote('What is this about?');

        $expectedResult = 'NOTE:What is this about?';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddProductId(): void
    {
        $vcard = new Vcard();
        $vcard->addProductId('My vCard Application');

        $expectedResult = 'PRODID:My vCard Application';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddRevision(): void
    {
        $vcard = new Vcard();
        $vcard->addRevision('2017-12-13');

        $expectedResult = 'REV:2017-12-13T00:00:00Z';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddSortString(): void
    {
        $vcard = new Vcard();
        $vcard->addSortString('Doe');

        $expectedResult = 'SORT-STRING:Doe';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddSound(): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addSound('Test');

        $expectedResult = null;
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddUniqueIdentifier(): void
    {
        $vcard = new Vcard();
        $vcard->addUniqueIdentifier('ID-1234567');

        $expectedResult = 'UID:ID-1234567';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddUrlValid(): void
    {
        $vcard = new Vcard();
        $vcard->addUrl('https://www.jaredhowland.com');

        $expectedResult = 'URL:https://www.jaredhowland.com';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddUrlInvalid(): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addUrl('jaredhowland');

        $expectedResult = null;
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddClassificationValid(): void
    {
        $vcard = new Vcard();
        $vcard->addClassification('PRIVATE');

        $expectedResult = 'CLASS:PRIVATE';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddClassificationInvalid(): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addClassification('jaredhowland');

        $expectedResult = null;
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddKey(): void
    {
        $this->expectException(ContactsException::class);

        $vcard = new Vcard();
        $vcard->addKey('Test');

        $expectedResult = null;
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddExtendedType(): void
    {
        $vcard = new Vcard();
        $vcard->addExtendedType('TWITTER', '@jared_howland');

        $expectedResult = 'X-TWITTER:@jared_howland';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddAnniversary(): void
    {
        $vcard = new Vcard();
        $vcard->addAnniversary('2017-12-13');

        $expectedResult = 'item1.X-ABDATE;type=pref:2017-12-13\r\nitem1.X-ABLabel:_$!<Anniversary>!$_';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddSupervisor(): void
    {
        $vcard = new Vcard();
        $vcard->addSupervisor('Jennifer');

        $expectedResult = 'item1.X-ABRELATEDNAMES:Jennifer\r\nitem1.X-ABLabel:_$!<Manager>!$_';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddSpouse(): void
    {
        $vcard = new Vcard();
        $vcard->addSpouse('John');

        $expectedResult = 'item1.X-ABRELATEDNAMES:John\r\nitem1.X-ABLabel:_$!<Spouse>!$_';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddChild(): void
    {
        $vcard = new Vcard();
        $vcard->addChild('Emily');

        $expectedResult = 'item1.X-ABRELATEDNAMES:Emily\r\nitem1.X-ABLabel:_$!<Child>!$_';
        $result         = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testBuildVcard(): void
    {
        $vcard = new Vcard('./tests/files/');

        $vcard->addName('Doe', 'Jane', 'Mary');
        $vcard->addRole('Accountant');
        $vcard->addChild('Emily');
        $vcard->addRevision('2017-12-13');

        $expectedResult = "BEGIN:VCARD\r\nVERSION:3.0\r\nN:Doe;Jane;Mary;;\r\nROLE:Accountant\r\nitem1.X-ABRELATEDNAMES:Emily\r\nitem1.X-ABLabel:_\$!<Child>!\$_\r\nREV:2017-12-13T00:00:00Z\r\nEND:VCARD\r\n\r\n";
        $result         = $vcard->buildVcard(true, 'test');

        $this->assertFileExists('./tests/files/test.vcf');
        $this->assertEquals($expectedResult, $result);
        unlink('./tests/files/test.vcf');
    }
}
