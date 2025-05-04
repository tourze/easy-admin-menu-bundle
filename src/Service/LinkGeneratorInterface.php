<?php

namespace Tourze\EasyAdminMenuBundle\Service;

interface LinkGeneratorInterface
{
    /**
     * CURD列表路径
     */
    public function getCurdListPage(string $entityClass): string;

    /**
     * 从URL中提取实体类名
     */
    public function extractEntityFqcn(string $url): ?string;
}
