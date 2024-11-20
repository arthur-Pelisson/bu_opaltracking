<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Others;

interface  DotNetTypeCode {

	// Résumé :
	//     Référence null.
	const DotNetEmpty = 0;
	//
	// Résumé :
	//     Type général représentant une référence ou type valeur non explicitement
	//     représenté par un autre TypeCode.
	const DotNetObject = 1;
	//
	// Résumé :
	//     Valeur null de base de données (colonne).
	const DotNetDBNull = 2;
	//
	// Résumé :
	//     Type simple représentant les valeurs booléennes de true ou false.
	const DotNetBoolean = 3;
	//
	// Résumé :
	//     Type intégral représentant des entiers 16 bits non signés dont la valeur
	//     est comprise entre 0 et 65535. Le jeu de valeurs possibles pour le type System.TypeCode.Char
	//     correspond au jeu de caractères Unicode.
	const DotNetChar = 4;
	//
	// Résumé :
	//     Type intégral représentant des entiers 8 bits signés dont la valeur est comprise
	//     entre -128 et 127.
	const DotNetSByte = 5;
	//
	// Résumé :
	//     Type intégral représentant des entiers 8 bits non signés dont la valeur est
	//     comprise entre 0 et 255.
	const DotNetByte = 6;
	//
	// Résumé :
	//     Type intégral représentant des entiers 16 bits signés dont la valeur est
	//     comprise entre -32768 et 32767.
	const DotNetInt16 = 7;
	//
	// Résumé :
	//     Type intégral représentant des entiers 16 bits non signés dont la valeur
	//     est comprise entre 0 et 65535.
	const DotNetUInt16 = 8;
	//
	// Résumé :
	//     Type intégral représentant des entiers 32 bits signés dont la valeur est
	//     comprise entre -2147483648 et 2147483647.
	const DotNetInt32 = 9;
	//
	// Résumé :
	//     Type intégral représentant des entiers 32 bits non signés dont la valeur
	//     est comprise entre 0 et 4294967295.
	const DotNetUInt32 = 10;
	//
	// Résumé :
	//     Type intégral représentant des entiers 64 bits signés dont la valeur est
	//     comprise entre -9223372036854775808 et 9223372036854775807.
	const DotNetInt64 = 11;
	//
	// Résumé :
	//     Type intégral représentant des entiers 64 bits non signés dont la valeur
	//     est comprise entre 0 et 18446744073709551615.
	const DotNetUInt64 = 12;
	//
	// Résumé :
	//     Type en virgule flottante représentant des valeurs comprises entre 1.5 x 10-45
	//     et 3.4 x 1038 environ; avec une précision de 7 chiffres.
	const DotNetSingle = 13;
	//
	// Résumé :
	//     Type en virgule flottante représentant des valeurs comprises entre 5.0 x 10-324
	//     et 1.7 x 10308 environ; avec une précision de 15-16 chiffres.
	const DotNetDouble = 14;
	//
	// Résumé :
	//     Type simple représentant des valeurs comprises entre 1.0 x 10-28 et environ
	//     7.9 x 1028; avec 28-29 chiffres significatifs.
	const DotNetDecimal = 15;
	//
	// Résumé :
	//     Type représentant une valeur de date et d'heure.
	const DotNetDateTime = 16;
	//
	// Résumé :
	//     Type classe sealed représentant des chaînes de caractères Unicode.
	const DotNetString = 18;
}

