<?php

/**
* Deep Zoom Tools
*
* Copyright (c) 2008-2009, OpenZoom <http://openzoom.org/>
* Copyright (c) 2008-2009, Nicolas Fabre <nicolas.fabre@gmail.com>
* All rights reserved.
*
* Redistribution and use in source and binary forms, with or without modification,
* are permitted provided that the following conditions are met:
*
* 1. Redistributions of source code must retain the above copyright notice,
* this list of conditions and the following disclaimer.
*
* 2. Redistributions in binary form must reproduce the above copyright
* notice, this list of conditions and the following disclaimer in the
* documentation and/or other materials provided with the distribution.
*
* 3. Neither the name of OpenZoom nor the names of its contributors may be used
* to endorse or promote products derived from this software without
* specific prior written permission.
*
* THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
* ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
* WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
* DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
* ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
* (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
* LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
* ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
* (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
* SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
* 
*/

/**
 *
 * @author Nicolas Fabre <nicolas.fabre@gmail.com>
 */
class Flexphperia_OzDeepzoomDescriptor
{

    /**
     * Descriptor txt file template
     */
    const DZI_TEMPLATE = '%s|%s|Upload date: %s';

    /**
     * Width of the original image
     *
     * @var int
     */
    protected $_width;

    /**
     * Height of the original image
     *
     * @var int
     */
    protected $_height;

    /**
     * Tile size
     *
     * @var int
     */
    protected $_tileSize;

    /**
     * Tile overlap
     *
     * @var float
     */
    protected $_tileOverlap;

    /**
     * Number of levels in the pyramid
     *
     * @var int
     */
    protected $_numLevels = null;

    /**
     * Constructor
     *
     * @param int $width            
     * @param int $height            
     * @param int $tileSize            
     * @param int $tileOverlap            
     * @param string $tileFormat            
     * @return void
     */
    public function __construct($width = null, $height = null, $tileSize = 256, $tileOverlap = 0)
    {
        $this->_width = $width;
        $this->_height = $height;
        $this->_tileSize = $tileSize;
        $this->_tileOverlap = $tileOverlap;
    }

    /**
     *
     * @param string $name            
     * @return mixed
     */
    public function __get($name)
    {
        return $this->{'_' . $name};
    }

    /**
     * Save descriptor file
     *
     * @param string $source            
     * @return Oz_Deepzoom_Descriptor
     */
    public function save($source)
    {
        file_put_contents($source, sprintf(self::DZI_TEMPLATE, $this->_width, $this->_height, date('d-m-Y')));
        return $this;
    }

    /**
     * Number of levels in the pyramid
     *
     * @return int
     */
    public function numLevels()
    {
        if (empty($this->_numLevels)) {
            $level_width = $this->_width;
            $level_height = $this->_height;
            $min_size = ($this->_tileSize / 2) + 1;
            $levelNum = 0;
            while ($level_width > $min_size || $level_height > $min_size) {
                $levelNum ++;
                $level_width = floor($level_width / 2);
                $level_height = floor($level_height / 2);
            }
            $this->_numLevels = $levelNum;
        }
        return $this->_numLevels;
    }

    /**
     * Scale of a pyramid level
     *
     * @param int $level            
     * @return float
     */
    public function getScale($level)
    {
        if (0 <= $level and $level < $this->numLevels())
            return pow(0.5, $level);
        else
            throw new Exception("Invalid pyramid level (scale)");
    }

    /**
     * Dimensions of level (width, height)
     *
     * @param int $level            
     * @return array
     */
    public function getDimension($level)
    {
        if (0 <= $level and $level < $this->numLevels()) {
            $scale = $this->getScale($level);
            $width = (int) ceil($this->_width * $scale);
            $height = (int) ceil($this->_height * $scale);
            return array(
                $width,
                $height
            );
        } else
            throw new Exception("Invalid pyramid level (dimension)");
    }

    /**
     * Number of tiles (columns, rows)
     *
     * @param int $level            
     * @return array
     */
    public function getNumTiles($level)
    {
        if (0 <= $level and $level < $this->numLevels()) {
            list ($width, $height) = $this->getDimension($level);
            $columns = (int) ceil(floatval($width) / $this->_tileSize);
            $rows = (int) ceil(floatval($height) / $this->_tileSize);
            return array(
                $columns,
                $rows
            );
        } else
            throw new Exception("Invalid pyramid level (NumTiles)");
    }

    /**
     * Bounding box of the tile (x1, y1, width, height)
     *
     * @param int $level
     *            pyramid level
     * @param int $column            
     * @param int $row            
     * @return array
     */
    public function getTileBounds($level, $column, $row)
    {
        if (0 <= $level and $level < $this->numLevels()) {
            $offsetX = $column == 0 ? 0 : $this->_tileOverlap;
            $offsetY = $row == 0 ? 0 : $this->_tileOverlap;
            $x = ($column * $this->_tileSize) - $offsetX;
            $y = ($row * $this->_tileSize) - $offsetY;
            
            list ($levelWidth, $levelHeight) = $this->getDimension($level);
            $width = $this->_tileSize + ($column == 0 ? 1 : 2) * $this->_tileOverlap;
            $height = $this->_tileSize + ($row == 0 ? 1 : 2) * $this->_tileOverlap;
            $width = min($width, $levelWidth - $x);
            $height = min($height, $levelHeight - $y);
            return array(
                $x,
                $y,
                $width,
                $height
            );
        } else
            throw new Exception("Invalid pyramid level (TileBounds)");
    }
}