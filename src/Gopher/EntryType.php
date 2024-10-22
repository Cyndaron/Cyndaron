<?php
declare(strict_types=1);

namespace Cyndaron\Gopher;

enum EntryType : string
{
    // Base types
    case TextFile = '0';
    case Directory = '1';
    case CCSONameserver = '2';
    case Error = '3';
    case BinHexFile = '4';
    case DOSFile = '5';
    case UUEncodedFile = '6';
    case GopherFulltextSearch = '7';
    case Telnet = '8';
    case BinaryFile = '9';
    case Mirror = '+';
    case GIFFile = 'g';
    case ImageFile = 'I';
    case Telnet3720 = 'T';

    // Gopher+
    case BitmapImage = ':';
    case MovieFile = ';';
    case SoundFile = '<';

    // Unofficial
    case Document = 'd';
    case HTMLFile = 'h';
    case Information = 'i';
    case PNGFile = 'p';
    case RTFFile = 'r';
    case WAVFile = 's';
    case PDFFile = 'P';
    case XMLFile = 'X';
}
