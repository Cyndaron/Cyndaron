# Cyndaron
CMS that aims for simplicity and security. Available onder the MIT license, see the LICENSE file for details.

Licences for third-party code (and all changes made to it):
- Lightbox: MIT (LICENSE.Lightbox)
- MCServerStats: MIT (LICENSE.MCServerStats)
- MinecraftSkinRenderer: BSD-3 (LICENSE.MinecraftSkinRenderer)

N.B.: Licences of third-party code not directly included in the code base can be found in their respective directories under the “vendor” folder.

## Code style
De code style used is [PSR-12](http://www.php-fig.org/psr/psr-12/), with the following changes:
- All braces are on their own line.
- Braces on if blocks are not required, provided both `if` and `else` instructions (if present) only have a single instruction.

And the following requirement:
- Arrays should always use short syntax.
