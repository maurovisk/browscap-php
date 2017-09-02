<?php
declare(strict_types = 1);

namespace BrowscapPHPTest\Helper\Converter;

use BrowscapPHP\Cache\BrowscapCacheInterface;
use BrowscapPHP\Exception\FileNotFoundException;
use BrowscapPHP\Helper\Converter;
use BrowscapPHP\Helper\Filesystem;
use org\bovigo\vfs\vfsStream;
use Psr\Log\LoggerInterface;

/**
 * @covers \BrowscapPHP\Helper\Converter
 */
final class ConverterTest extends \PHPUnit\Framework\TestCase
{
    const STORAGE_DIR = 'storage';

    /**
     * @var \BrowscapPHP\Helper\Converter
     */
    private $object;

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    public function setUp() : void
    {
        /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())
               ->method('info')
               ->will(self::returnValue(false));

        /** @var BrowscapCacheInterface|\PHPUnit_Framework_MockObject_MockObject $cache */
        $cache = $this->createMock(BrowscapCacheInterface::class);
        $cache->expects(self::any())
              ->method('setItem')
              ->will(self::returnValue(true));

        $this->object = new Converter($logger, $cache);
    }

    public function testSetGetFilesystem() : void
    {
        self::assertInstanceOf(Filesystem::class, $this->object->getFilesystem());

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $file */
        $file = $this->createMock(Filesystem::class);

        $this->object->setFilesystem($file);
        self::assertSame($file, $this->object->getFilesystem());
    }

    public function testConvertMissingFile() : void
    {
        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $file */
        $file = $this->createMock(Filesystem::class);
        $file->expects(self::once())
            ->method('exists')
            ->will(self::returnValue(false));

        $this->object->setFilesystem($file);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('testFile');
        $this->object->convertFile('testFile');
    }

    public function testConvertFile() : void
    {
        $content = ';;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;; Browscap Version

[GJK_Browscap_Version]
Version=5031
Released=Mon, 30 Jun 2014 17:55:58 +0200
Format=ASP
Type=

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;; DefaultProperties

[DefaultProperties]

Comment=DefaultProperties
Browser=DefaultProperties
Version=0.0
MajorVer=0
MinorVer=0
Platform=unknown
Platform_Version=unknown
Alpha=false
Beta=false
Win16=false
Win32=false
Win64=false
Frames=false
IFrames=false
Tables=false
Cookies=false
BackgroundSounds=false
JavaScript=false
VBScript=false
JavaApplets=false
ActiveXControls=false
isMobileDevice=false
isTablet=false
isSyndicationReader=false
Crawler=false
CssVersion=0
AolVersion=0

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;; Ask

[Ask]

Parent=DefaultProperties
Comment=Ask
Browser=Ask
Frames=1
IFrames=1
Tables=1
Crawler=1
Version=0.0
MajorVer=0
MinorVer=0
Platform=unknown
Platform_Version=unknown
Alpha=
Beta=
Win16=
Win32=
Win64=
Cookies=
BackgroundSounds=
JavaScript=
VBScript=
JavaApplets=
ActiveXControls=
isMobileDevice=
isTablet=
isSyndicationReader=
CssVersion=0
AolVersion=0

[Mozilla/?.0 (compatible; Ask Jeeves/Teoma*)]

Parent=Ask
Browser=Teoma
Comment=Ask
Version=0.0
MajorVer=0
MinorVer=0
Platform=unknown
Platform_Version=unknown
Alpha=
Beta=
Win16=
Win32=
Win64=
Frames=1
IFrames=1
Tables=1
Cookies=
BackgroundSounds=
JavaScript=
VBScript=
JavaApplets=
ActiveXControls=
isMobileDevice=
isTablet=
isSyndicationReader=
Crawler=1
CssVersion=0
AolVersion=0
';
        $structure = [
            self::STORAGE_DIR => [
                'test.ini' => $content,
            ],
        ];

        $this->root = vfsStream::setup(self::STORAGE_DIR, null, $structure);

        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $file */
        $file = $this->createMock(Filesystem::class);
        $file->expects(self::once())
            ->method('exists')
            ->will(self::returnValue(false));

        $this->object->setFilesystem($file);

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File "vfs://storage/test.ini" does not exist');
        $this->object->convertFile(vfsStream::url(self::STORAGE_DIR . DIRECTORY_SEPARATOR . 'test.ini'));
    }

    public function testGetIniVersion() : void
    {
        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $file */
        $file = $this->createMock(Filesystem::class);
        $file->expects(self::never())
            ->method('exists')
            ->will(self::returnValue(false));

        $this->object->setFilesystem($file);

        $content = ';;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;; Browscap Version

[GJK_Browscap_Version]
Version=5031
Released=Mon, 30 Jun 2014 17:55:58 +0200
Format=ASP
Type=';

        self::assertSame(5031, $this->object->getIniVersion($content));
    }
}
