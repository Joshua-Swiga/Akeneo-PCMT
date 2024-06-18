# License How-to

All files created for PCMT should be marked with a copyright and license.
In all examples below the year should be updated to the current year in the
copyright declaration.

## Source Code

Usually marked in the header of the file. We'll include the 
[SPDX Identifier][spdx-using] for machine readability.

Example header:

```
Copyright (c) 2019, VillageReach
Licensed under the Non-Profit Open Software License version 3.0.
SPDX-License-Identifier: NPOSL-3.0
```

For files from Akeneo CE PIM modified for use in PCMT (in PCMT), we should
mark a dual copyright and license:

```
Copyright (c) 2013, Akeneo SAS
Copyright (c) 2019, VillageReach
Licensed under the Open Software License version 3.0 AND Non-Profit Open 
Software License version 3.0.
SPDX-License-Identifier: NPOSL-3.0 AND OSL-3.0
```

### Markdown, YAML, Terraform, Shell etc

PCMT:
```
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################
```

PCMT and Akeneo:
```
################################################################################
# Copyright (c) 2013, Akeneo SAS
# Copyright (c) 2019, VillageReach
# Licensed under the Open Software License version 3.0 AND Non-Profit Open 
# Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0 AND OSL-3.0
################################################################################
```

### PHP

PCMT:
```
/**********************************************************************
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
**********************************************************************/
```

PCMT and Akeneo:
```
/*******************************************************************************
 * Copyright (c) 2013, Akeneo SAS
 * Copyright (c) 2019, VillageReach
 * Licensed under the Open Software License version 3.0 AND Non-Profit Open 
 * Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0 AND OSL-3.0
*******************************************************************************/
```

## Documentation

Text files, word processing documents, or presentations usually will include 
the copyright and license declaration either on a cover page or in the footer 
of the document.

Example:

```
Copyright (c) 2019, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/
```

The full URL of the license should always be visible.

[spdx-using]: https://spdx.org/using-spdx-license-identifier

---
Copyright (c) 2019, VillageReach.  Licensed CC BY-SA 4.0:  https://creativecommons.org/licenses/by-sa/4.0/

## Setting Copyrights in PhpStorm

1. Go to Settings
1. Go to Editor > Copyright > Copyright profiles
1. Add one with a suggested name "PCMT" and text:
    ```
    Copyright (c) $today.year, VillageReach
    Licensed under the Non-Profit Open Software License version 3.0.
    SPDX-License-Identifier: NPOSL-3.0
    ```
1. Go to Editor > Copyright
1. Add profile "PCMT" to scope "Project files"
1. Go to Editor > Copyright > Formatting and set "Use default settings" in all used file types
1. To test, try creating php, js or html file in the project folder.
 