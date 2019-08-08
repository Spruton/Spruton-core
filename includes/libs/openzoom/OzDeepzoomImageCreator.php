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
* @flexphperia - some changes, to support imgcnv tiles
*/

/**
 * Creates Deep Zoom images
 *
 * @author Nicolas Fabre <nicolas.fabre@gmail.com>
 */
class Flexphperia_OzDeepzoomImageCreator
{

    /**
     *
     * @var string
     */
    protected $_tileSize;

    /**
     *
     * @var float
     */
    protected $_tileOverlap;

    protected $_tileFormat;

    protected $_imageQuality;

    /**
     *
     * @var Flexphperia_GdThumb
     */
    public $image;

    public $imageWidth;

    public $imageHeight;

    /**
     *
     * @var Flexphperia_OzDeepzoomDescriptor
     */
    protected $_descriptor;

    protected $_destination;

    /**
     * Constructor
     *
     * @param int $tileSize            
     * @param int $tileOverlap            
     * @param string $tileFormat            
     * @param float $imageQuality            
     */
    public function __construct($source, $destination, $tileSize = 256, $tileOverlap = 0, $tileFormat = "png", $imageQuality = 97)
    {
        $this->_tileSize = (int) $tileSize;
        $this->_tileFormat = $tileFormat;
        $this->_imageQuality = $imageQuality;
        $this->_tileOverlap = $this->_clamp((int) $tileOverlap, 0, 1);
        $this->_destination = $destination;
        
        $this->image = new Flexphperia_GdThumb($source, array(
            'jpegQuality' => $imageQuality,
            'outputFormat' => $tileFormat
        ));
        
        $imgSize = $this->image->getCurrentDimensions();
        $this->imageWidth = $imgSize['width'];
        $this->imageHeight = $imgSize['height'];
        
        $this->_descriptor = new Flexphperia_OzDeepzoomDescriptor($this->imageWidth, $this->imageHeight, 
            $this->_tileSize, $this->_tileOverlap);
    }

    /**
     * Returns the bitmap image at the given level
     *
     * @param int $level            
     *
     * @return Flexphperia_GdThumb
     */
    public function getImage($level)
    {
        if (0 <= $level and $level < $this->_descriptor->numLevels()) {
            list ($width, $height) = $this->_descriptor->getDimension($level);
            
            // don't transform to what we already have
            if ($this->_descriptor->width == $width and $this->_descriptor->height == $height) {
                return $this->image;
            } else {
                $image = clone $this->image;
                $image->resize($width, $height);
                return $image;
            }
        } else
            new Exception('Invalid pyramid level');
    }

    /**
     * Iterator for all tiles in the given level.
     * Returns (column, row) of a tile.
     *
     * @param int $level            
     *
     * @return array
     */
    public function tiles($level)
    {
        list ($columns, $rows) = $this->_descriptor->getNumTiles($level);
        $yield = array();
        foreach (range(0, $columns - 1) as $column) {
            foreach (range(0, $rows - 1) as $row) {
                $yield[] = array(
                    $column,
                    $row
                );
            }
        }
        return $yield;
    }

    /**
     * Creates Deep Zoom image from source file and saves it to destination
     *
     * @param string $source            
     * @param string $destination            
     */
    public function create()
    {
        $leafletMaxZoom = 18;
    
        for ($i = 0; $i < $this->_descriptor->numLevels(); $i ++) {
            $levelImage = $this->getImage($i);
            $tiles = $this->tiles($i);
            foreach ($tiles as $_tile) {
                list ($column, $row) = $_tile;
                list ($x, $y, $x2, $y2) = $this->_descriptor->getTileBounds($i, $column, $row);
                $cropLevelImage = clone $levelImage;
                // $cropLevelImage->crop($x, $y, $x2, $y2); //always square tiles
				$cropLevelImage->crop($x, $y, $x2, $y2, $this->_tileSize); //always square tiles
                
                $tilePath = $this->_destination . DIRECTORY_SEPARATOR .
                     sprintf('map_%s_%s_%s.%s', $leafletMaxZoom - $i, $column, 
                        $row, $this->_tileFormat);
                    
                    $cropLevelImage->save($tilePath, $this->_tileFormat);
                    unset($cropLevelImage);
                }
                unset($levelImage);
            }
            $this->_descriptor->save($this->_destination . DIRECTORY_SEPARATOR . 'descriptor.txt');
        }

        protected function _formatInt($n, $pad = 3)
        {
            $n = (string) $n;
            return str_pad($n, $pad, '0', STR_PAD_LEFT);
        }

        /**
         *
         * @param int $val            
         * @param int $min            
         * @param int $max            
         *
         * @return int
         */
        protected function _clamp($val, $min, $max)
        {
            if ($val < $min) {
                return $min;
            } elseif ($val > $max) {
                return $max;
            }
            return $val;
        }
    
    }
