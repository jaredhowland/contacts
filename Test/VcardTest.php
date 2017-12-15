<?php declare(strict_types = 1);
/**
 * Tests for the Vcard class
 *
 * @author  Jared Howland <contacts@jaredhowland.com>
 * @version 2017-12-12
 * @since   2017-12-12
 *
 */

namespace Contacts\Test;

use Contacts\Vcard;
use PHPUnit\Framework\TestCase;

class VcardTest extends TestCase
{
    public function testDebugReturnsString()
    {
        $vcard = new Vcard();

        $expectedResult = "<pre>**PROPERTIES**\n1\n\n**DEFINED ELEMENTS**\n1";
        $result = $vcard->debug();

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetPropertiesReturnsAllProperties()
    {
        $vcard = new Vcard();
        $vcard->addFullName('Jane Doe');

        $expectedResult = 'FN:Jane Doe';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetDefinedElementsReturnsAllProperties()
    {
        $vcard = new Vcard();
        $vcard->addFullName('Jane Doe');

        $expectedResult = '1';
        $result = $vcard->getDefinedElements()['FN'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddFullName()
    {
        $vcard = new Vcard();
        $vcard->addFullName('Jane Doe');

        $expectedResult = 'FN:Jane Doe';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddName()
    {
        $vcard = new Vcard();
        $vcard->addName('Doe', 'Jane', 'Mary, Elizabeth', 'Mrs., Dr.', 'PhD, MD');

        $expectedResult = 'N:Doe;Jane;Mary,Elizabeth;Mrs.,Dr.;PhD,MD';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddNickname()
    {
        $vcard = new Vcard();
        $vcard->addNicknames(['Jan', 'Janet']);

        $expectedResult = 'NICKNAME:Jan,Janet';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddPhotoUrl()
    {
        $vcard = new Vcard();
        $vcard->addPhoto('https://raw.githubusercontent.com/jaredhowland/contacts/master/Test/files/photo.jpg');

        $expectedResult = file_get_contents('Test/files/expectedPhoto.txt');
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddPhotoBinary()
    {
        $vcard = new Vcard();
        $vcard->addPhoto(file_get_contents('Test/files/photoBinary.txt'), false);

        $expectedResult = file_get_contents('Test/files/expectedPhoto.txt');
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddBirthdayWithYear()
    {
        $vcard = new Vcard();
        $vcard->addBirthday(null, 10, 5);

        $expectedResult = 'BDAY;X-APPLE-OMIT-YEAR=1604:1604-10-05';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddBirthdayWithoutYear()
    {
        $vcard = new Vcard();
        $vcard->addBirthday(1980, 10, 5);

        $expectedResult = 'BDAY:1980-10-05';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddAddress()
    {
        $vcard = new Vcard();
        $vcard->addAddress('P.O. Box 1105', 'Big Corporation', '1540 Main St.', 'Provo', 'UT', '84602', 'USA', ['postal', 'parcel', 'home']);

        $expectedResult = 'ADR;TYPE=postal,parcel,home:P.O. Box 1105;Big Corporation;1540 Main St.;Provo;UT;84602;USA';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddLabel()
    {
        $vcard = new Vcard();
        $vcard->addLabel('Big Corporation\n1105 Main St.\nProvo, UT 84602\nU.S.A.', ['home', 'postal', 'parcel']);

        $expectedResult = 'LABEL;TYPE=home,postal,parcel:Big Corporation\n1105 Main St.\nProvo\, UT 84602\nU.S.A.';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddTelephoneWithAreaCode()
    {
        $vcard = new Vcard();
        $vcard->addTelephone('709.567-9087', ['cell', 'iphone', 'pref']);

        $expectedResult = 'TEL;TYPE=cell,iphone,pref:(709) 567-9087';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddTelephoneWithoutAreaCode()
    {
        $vcard = new Vcard();
        $vcard->addTelephone('567-9087', ['cell', 'pref']);

        $expectedResult = 'TEL;TYPE=cell,pref:(801) 567-9087';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddTelephoneInvalid()
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addTelephone('709.567-90871', ['cell', 'iphone', 'pref']);

        $expectedResult = null;
        $result = $vcard->getProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddEmailValid()
    {
        $vcard = new Vcard();
        $vcard->addEmail('test@test.com', ['internet', 'pref']);

        $expectedResult = 'EMAIL;TYPE=internet,pref:test@test.com';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddEmailInvalid()
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addEmail('test.com', ['internet', 'pref']);

        $expectedResult = null;
        $result = $vcard->getProperties();

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddMailer()
    {
        $vcard = new Vcard();
        $vcard->addMailer('Outlook');

        $expectedResult = 'MAILER:Outlook';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @param string $originalTimeZone Time zone as passed to `addTimeZone` method
     * @param string $expectedResult   Expected value
     *
     * @dataProvider providerTestAddTimeZoneValid
     */
    public function testAddTimeZoneValid($originalTimeZone, $expectedResult)
    {
        $vcard = new Vcard();
        $vcard->addTimeZone($originalTimeZone);

        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestAddTimeZoneValid()
    {
        return [
            ['-7', 'TZ:-07:00'],
            ['7:30', 'TZ:+07:30'],
            ['+7:30', 'TZ:+07:30'],
            ['-07', 'TZ:-07:00'],
            ['-7:00', 'TZ:-07:00'],
            ['-07:30', 'TZ:-07:30'],
            ['-14', 'TZ:-14:00']
        ];
    }

    /**
     * @param string $originalTimeZone Time zone as passed to `addTimeZone` method
     * @param string $expectedResult   Expected value
     *
     * @dataProvider providerTestAddTimeZoneInvalid
     */
    public function testAddTimeZoneInvalid($originalTimeZone, $expectedResult)
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addTimeZone($originalTimeZone);

        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestAddTimeZoneInvalid()
    {
        return [
            ['', null],
            ['-15', null],
            ['13:30', null],
            ['-00:00', null],
            ['monkey', null]
        ];
    }

    /**
     * @param string $originalLatLong Latitude & longitude coordinates as passed to `addLatLong` method
     * @param string $expectedResult  Expected value
     *
     * @dataProvider providerTestAddLatLongValid
     */
    public function testAddLatLongValid($originalLat, $originalLong, $expectedResult)
    {
        $vcard = new Vcard();
        $vcard->addLatLong($originalLat, $originalLong);

        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestAddLatLongValid()
    {
        return [
            ['0', '0', 'GEO:0.000000;0.000000'],
            ['-89.123456', '179.654321', 'GEO:-89.123456;179.654321'],
            ['-90', '-180', 'GEO:-90.000000;-180.000000'],
            ['90', '180', 'GEO:90.000000;180.000000']
        ];
    }

    /**
     * @param string $originalLatLong Latitude & longitude coordinates as passed to `addLatLong` method
     * @param string $expectedResult  Expected value
     *
     * @dataProvider providerTestAddLatLongInvalid
     */
    public function testAddLatLongInvalid($originalLat, $originalLong, $expectedResult)
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addLatLong($originalLat, $originalLong);

        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function providerTestAddLatLongInvalid()
    {
        return [
            ['-90.123456', '180.654321', null],
            ['seven', 'eight', null],
            ['hi', 'bye', null],
            ['', '', null]
        ];
    }

    public function testAddTitle()
    {
        $vcard = new Vcard();
        $vcard->addTitle('CEO');

        $expectedResult = 'TITLE:CEO';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddRole()
    {
        $vcard = new Vcard();
        $vcard->addRole('CEO');

        $expectedResult = 'ROLE:CEO';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddLogoUrl()
    {
        $vcard = new Vcard();
        $vcard->addLogo('https://raw.githubusercontent.com/jaredhowland/contacts/master/Test/files/photo.jpg');

        $expectedResult = file_get_contents('Test/files/expectedPhoto.txt');
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddLogoBinary()
    {
        $vcard = new Vcard();
        $vcard->addLogo(file_get_contents('Test/files/photoBinary.txt'), false);

        $expectedResult = file_get_contents('Test/files/expectedPhoto.txt');
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddAgent()
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addAgent('Test');

        $expectedResult = null;
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddOrganizations()
    {
        $vcard = new Vcard();
        $vcard->addOrganizations(['Big Organization 1', 'Big Organization 2']);

        $expectedResult = 'ORG:Big Organization 1;Big Organization 2';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddCategories()
    {
        $vcard = new Vcard();
        $vcard->addCategories(['Home', 'Work']);

        $expectedResult = 'CATEGORIES:Home,Work';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddNote()
    {
        $vcard = new Vcard();
        $vcard->addNote('What is this about?');

        $expectedResult = 'NOTE:What is this about?';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddProductId()
    {
        $vcard = new Vcard();
        $vcard->addProductId('My vCard Application');

        $expectedResult = 'PRODID:My vCard Application';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddRevision()
    {
        $vcard = new Vcard();
        $vcard->addRevision('2017-12-13');

        $expectedResult = 'REV:2017-12-13T00:00:00Z';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddSortString()
    {
        $vcard = new Vcard();
        $vcard->addSortString('Doe');

        $expectedResult = 'SORT-STRING:Doe';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddSound()
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addSound('Test');

        $expectedResult = null;
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddUniqueIdentifier()
    {
        $vcard = new Vcard();
        $vcard->addUniqueIdentifier('ID-1234567');

        $expectedResult = 'UID:ID-1234567';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddUrlValid()
    {
        $vcard = new Vcard();
        $vcard->addUrl('http://jaredhowland.com');

        $expectedResult = 'URL:http://jaredhowland.com';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddUrlInvalid()
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addUrl('jaredhowland');

        $expectedResult = null;
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddClassificationValid()
    {
        $vcard = new Vcard();
        $vcard->addClassification('PRIVATE');

        $expectedResult = 'CLASS:PRIVATE';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddClassificationInvalid()
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addClassification('jaredhowland');

        $expectedResult = null;
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddKey()
    {
        $this->expectException('Contacts\ContactsException');

        $vcard = new Vcard();
        $vcard->addKey('Test');

        $expectedResult = null;
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddExtendedType()
    {
        $vcard = new Vcard();
        $vcard->addExtendedType('TWITTER', '@jared_howland');

        $expectedResult = 'X-TWITTER:@jared_howland';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddAnniversary()
    {
        $vcard = new Vcard();
        $vcard->addAnniversary('2017-12-13');

        $expectedResult = 'item1.X-ABDATE;type=pref:2017-12-13\r\nitem1.X-ABLabel:_$!<Anniversary>!$_';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddSupervisor()
    {
        $vcard = new Vcard();
        $vcard->addSupervisor('Jennifer');

        $expectedResult = 'item1.X-ABRELATEDNAMES:Jennifer\r\nitem1.X-ABLabel:_$!<Manager>!$_';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddSpouse()
    {
        $vcard = new Vcard();
        $vcard->addSpouse('John');

        $expectedResult = 'item1.X-ABRELATEDNAMES:John\r\nitem1.X-ABLabel:_$!<Spouse>!$_';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testAddChild()
    {
        $vcard = new Vcard();
        $vcard->addChild('Emily');

        $expectedResult = 'item1.X-ABRELATEDNAMES:Emily\r\nitem1.X-ABLabel:_$!<Child>!$_';
        $result = $vcard->getProperties()[0]['value'];

        $this->assertEquals($expectedResult, $result);
    }

    public function testBuildVcard()
    {
        $vcard = new Vcard('./Test/files/');

        $vcard->addName('Doe', 'Jane', 'Mary');
        $vcard->addRole('Accountant');
        $vcard->addChild('Emily');
        $vcard->addRevision('2017-12-13');

        $expectedResult = "BEGIN:VCARD\r\nVERSION:3.0\r\nN:Doe;Jane;Mary;;\r\nROLE:Accountant\r\nitem1.X-ABRELATEDNAMES:Emily\r\nitem1.X-ABLabel:_\$!<Child>!\$_\r\nREV:2017-12-13T00:00:00Z\r\nEND:VCARD\r\n\r\n";
        $result = $vcard->buildVcard(true, 'test');

        $this->assertFileExists('./Test/files/test.vcf');
        $this->assertEquals($expectedResult, $result);
        unlink('./Test/files/test.vcf');
    }
}
