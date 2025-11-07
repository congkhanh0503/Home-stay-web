<?php
if (!isset($homestay)) return;
?>

<div class="col-md-6 col-lg-4 mb-4">
    <div class="card homestay-card shadow-sm h-100">
        <!-- Image Slider -->
        <div id="carousel-<?php echo $homestay['id']; ?>" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php if (!empty($homestay['images'])): ?>
                    <?php foreach ($homestay['images'] as $index => $image): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo getImageUrl($image); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo $homestay['title']; ?>"
                                 style="height: 200px; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="carousel-item active">
                        <img src="<?php echo getImageUrl(''); ?>" 
                             class="card-img-top" 
                             alt="No image"
                             style="height: 200px; object-fit: cover;">
                    </div>
                <?php endif; ?>
            </div>
            <?php if (count($homestay['images']) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?php echo $homestay['id']; ?>" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?php echo $homestay['id']; ?>" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            <?php endif; ?>
        </div>
        
        <div class="card-body d-flex flex-column">
            <!-- Title and Price -->
            <h5 class="card-title"><?php echo $homestay['title']; ?></h5>
            <p class="card-text text-muted small">
                <i class="fas fa-map-marker-alt me-1"></i>
                <?php echo strlen($homestay['address']) > 50 ? substr($homestay['address'], 0, 50) . '...' : $homestay['address']; ?>
            </p>
            
            <!-- Amenities -->
            <?php if (!empty($homestay['amenities'])): ?>
                <div class="mb-2">
                    <?php $display_amenities = array_slice($homestay['amenities'], 0, 3); ?>
                    <?php foreach ($display_amenities as $amenity): ?>
                        <span class="badge bg-light text-dark me-1 mb-1">
                            <i class="fas fa-check me-1"></i><?php echo $amenity; ?>
                        </span>
                    <?php endforeach; ?>
                    <?php if (count($homestay['amenities']) > 3): ?>
                        <span class="badge bg-secondary">+<?php echo count($homestay['amenities']) - 3; ?> more</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Details -->
            <div class="row text-center mb-3">
                <div class="col-4">
                    <small class="text-muted">
                        <i class="fas fa-user-friends"></i><br>
                        <?php echo $homestay['max_guests']; ?> khách
                    </small>
                </div>
                <div class="col-4">
                    <small class="text-muted">
                        <i class="fas fa-bed"></i><br>
                        <?php echo $homestay['bedrooms']; ?> phòng
                    </small>
                </div>
                <div class="col-4">
                    <small class="text-muted">
                        <i class="fas fa-bath"></i><br>
                        <?php echo $homestay['bathrooms']; ?> tắm
                    </small>
                </div>
            </div>
            
            <!-- Price and Action -->
            <div class="mt-auto">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="text-primary mb-0"><?php echo formatPrice($homestay['price_per_night']); ?></h5>
                        <small class="text-muted">/ đêm</small>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/views/homestay/detail.php?id=<?php echo $homestay['id']; ?>" 
                       class="btn btn-primary btn-sm">
                        Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Host Info -->
        <div class="card-footer bg-transparent">
            <div class="d-flex align-items-center">
                <img src="<?php echo getImageUrl($homestay['host_avatar'] ?? ''); ?>" 
                     class="rounded-circle me-2" 
                     width="24" 
                     height="24" 
                     alt="Host">
                <small class="text-muted">Chủ nhà: <?php echo $homestay['host_name']; ?></small>
            </div>
        </div>
    </div>
</div>